<?php

namespace App\Services\Jobs;

use App\Models\FetchLog;
use App\Models\JobSource;
use Throwable;

class SourceFetchService
{
    public function __construct(
        private RssJobFetcher $fetcher,
        private JobListingService $listings,
    ) {
    }

    /**
     * Fetch one source, save new jobs and record what happened. Never throws:
     * a broken feed shows up as a failed log entry instead.
     */
    public function fetch(JobSource $source): FetchLog
    {
        $found = 0;
        $created = 0;

        try {
            $items = $this->fetcher->fetch($source->url);

            foreach ($items as $item) {
                $found++;
                $item['company'] = $item['company'] ?: $source->name;

                [, $isNew] = $this->listings->store($source->user, $item, $source);

                if ($isNew) {
                    $created++;
                }
            }

            $status = 'success';
            $error = null;
        } catch (Throwable $e) {
            $status = 'failed';
            $error = mb_substr($e->getMessage(), 0, 500);
        }

        $source->update(['last_fetched_at' => now()]);

        return $source->fetchLogs()->create([
            'status' => $status,
            'jobs_found' => $found,
            'jobs_created' => $created,
            'error' => $error,
        ]);
    }
}
