<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expire subscriptions daily
Schedule::command('subscriptions:expire')->daily();

// Prune activity logs older than 90 days (weekly to avoid large deletes)
Schedule::command('activitylog:clean --days=90')->weekly();

// Prune old personal access tokens
Schedule::command('sanctum:prune-expired --hours=24')->daily();
