<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Needs a scheduler running: `php artisan schedule:work` locally, or a cron entry on a server.
Schedule::command('jobmatch:fetch')->dailyAt('07:00')->timezone('Asia/Kuala_Lumpur');
Schedule::command('jobmatch:digest')->dailyAt('08:00')->timezone('Asia/Kuala_Lumpur');
