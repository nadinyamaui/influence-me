<?php

use App\Jobs\RefreshInstagramToken;
use App\Jobs\SyncAllInstagramData;
use App\Jobs\SyncInstagramProfile;
use App\Jobs\SyncMediaInsights;
use App\Models\InstagramAccount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    InstagramAccount::query()->each(function (InstagramAccount $account): void {
        SyncAllInstagramData::dispatch($account);
    });
})->everySixHours()->name('sync-all-instagram');

Schedule::call(function (): void {
    InstagramAccount::query()->each(function (InstagramAccount $account): void {
        SyncInstagramProfile::dispatch($account);
        SyncMediaInsights::dispatch($account);
    });
})->hourly()->name('refresh-instagram-insights');

Schedule::call(function (): void {
    $refreshWindowStart = now();
    $refreshWindowEnd = $refreshWindowStart->copy()->addDays(7);

    InstagramAccount::query()
        ->whereBetween('token_expires_at', [$refreshWindowStart, $refreshWindowEnd])
        ->each(function (InstagramAccount $account): void {
            RefreshInstagramToken::dispatch($account);
        });
})->daily()->name('refresh-instagram-tokens');
