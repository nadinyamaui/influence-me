<?php

use App\Enums\SyncStatus;
use App\Jobs\MarkInstagramSyncComplete;
use App\Jobs\SyncAllInstagramData;
use App\Jobs\SyncAudienceDemographics;
use App\Jobs\SyncInstagramMedia;
use App\Jobs\SyncInstagramProfile;
use App\Jobs\SyncInstagramStories;
use App\Jobs\SyncMediaInsights;
use App\Models\InstagramAccount;
use Illuminate\Support\Facades\Bus;

it('dispatches a full instagram sync chain in order and marks account syncing', function (): void {
    Bus::fake();

    $account = InstagramAccount::factory()->create([
        'sync_status' => SyncStatus::Idle,
        'last_sync_error' => 'old error',
    ]);

    app(SyncAllInstagramData::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Syncing)
        ->and($account->last_sync_error)->toBeNull();

    Bus::assertChained([
        SyncInstagramProfile::class,
        SyncInstagramMedia::class,
        SyncMediaInsights::class,
        SyncInstagramStories::class,
        SyncAudienceDemographics::class,
        MarkInstagramSyncComplete::class,
    ]);
});

it('updates account as failed when the chained sync catch callback is invoked', function (): void {
    Bus::fake();

    $account = InstagramAccount::factory()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => null,
    ]);

    app(SyncAllInstagramData::class, ['account' => $account])->handle();

    $firstJob = Bus::dispatched(SyncInstagramProfile::class)->first();
    $callback = $firstJob->chainCatchCallbacks[0];
    $callback(new \RuntimeException('Instagram sync chain failed.'));

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toBe('Instagram sync chain failed.');
});

it('configures the orchestrator to run on the instagram-sync queue', function (): void {
    $account = InstagramAccount::factory()->create();
    $job = new SyncAllInstagramData($account);

    expect($job->queue)->toBe('instagram-sync');
});

it('marks sync complete by setting idle status, sync timestamp, and clearing errors', function (): void {
    $account = InstagramAccount::factory()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_synced_at' => null,
        'last_sync_error' => 'old error',
    ]);

    app(MarkInstagramSyncComplete::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Idle)
        ->and($account->last_synced_at)->not->toBeNull()
        ->and($account->last_sync_error)->toBeNull();
});
