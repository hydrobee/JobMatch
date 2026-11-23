<?php

namespace App\Console\Commands;

use App\Models\JobSource;
use App\Services\Jobs\SourceFetchService;
use Illuminate\Console\Command;

class FetchJobs extends Command
{
    protected $signature = 'jobmatch:fetch';

    protected $description = 'Fetch new jobs from every enabled source';

    public function handle(SourceFetchService $service): int
    {
        $sources = JobSource::where('is_enabled', true)->with('user')->get();

        foreach ($sources as $source) {
            $log = $service->fetch($source);

            $this->line("{$source->name}: {$log->status}, {$log->jobs_created} new of {$log->jobs_found}".($log->error ? " ({$log->error})" : ''));
        }

        $this->info("Checked {$sources->count()} source(s).");

        return self::SUCCESS;
    }
}
