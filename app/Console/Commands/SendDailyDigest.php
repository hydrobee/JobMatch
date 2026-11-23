<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStage;
use App\Mail\DailyDigest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    protected $signature = 'jobmatch:digest';

    protected $description = 'Email new good matches and follow-ups that are due';

    public function handle(): int
    {
        $sent = 0;

        User::where('digest_enabled', true)->each(function (User $user) use (&$sent) {
            $jobs = $user->jobListings()
                ->where('is_expired', false)
                ->where('created_at', '>=', now()->subDay())
                ->where('match_score', '>=', $user->digest_min_score)
                ->orderByDesc('match_score')
                ->limit(15)
                ->get();

            $followUps = $user->applications()
                ->with('jobListing')
                ->whereNotNull('follow_up_at')
                ->whereDate('follow_up_at', '<=', now()->toDateString())
                ->whereIn('stage', [
                    ApplicationStage::Saved->value,
                    ApplicationStage::Applied->value,
                    ApplicationStage::Interview->value,
                ])
                ->orderBy('follow_up_at')
                ->get();

            if ($jobs->isEmpty() && $followUps->isEmpty()) {
                return;
            }

            Mail::to($user)->send(new DailyDigest($user, $jobs, $followUps));
            $sent++;
        });

        $this->info("Sent {$sent} digest(s).");

        return self::SUCCESS;
    }
}
