<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncUnsyncedPppSecrets;

// Run the sync every minute to catch reconnected routers
Schedule::job(new SyncUnsyncedPppSecrets())->everyMinute();
Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})->purpose("Display an inspiring quote");
