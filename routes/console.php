<?php

use App\Jobs\RecordFollowerSnapshot;
use App\Jobs\RefreshSocialMediaToken;
use App\Jobs\SyncAllSocialMediaData;
use App\Jobs\SyncSocialMediaProfile;
use App\Jobs\SyncMediaInsights;
use App\Models\SocialAccount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    SocialAccount::query()->each(function (SocialAccount $account): void {
        SyncAllSocialMediaData::dispatch($account);
    });
})->everySixHours()->name('sync-all-instagram');

Schedule::call(function (): void {
    SocialAccount::query()->each(function (SocialAccount $account): void {
        SyncSocialMediaProfile::dispatch($account);
        SyncMediaInsights::dispatch($account);
    });
})->hourly()->name('refresh-instagram-insights');

Schedule::call(function (): void {
    $refreshWindowStart = now();
    $refreshWindowEnd = $refreshWindowStart->copy()->addDays(7);

    SocialAccount::query()
        ->whereBetween('token_expires_at', [$refreshWindowStart, $refreshWindowEnd])
        ->each(function (SocialAccount $account): void {
            RefreshSocialMediaToken::dispatch($account);
        });
})->daily()->name('refresh-instagram-tokens');

Schedule::call(function (): void {
    SocialAccount::query()->each(function (SocialAccount $account): void {
        RecordFollowerSnapshot::dispatch($account);
    });
})->daily()->name('record-follower-snapshots');
