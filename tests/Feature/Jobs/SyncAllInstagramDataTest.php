<?php

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Jobs\SyncAllInstagramData;
use App\Models\InstagramAccount;
use App\Services\Facebook\InstagramGraphService;

it('runs the full instagram sync workflow and marks account as idle on success', function (): void {
    $account = InstagramAccount::factory()->create([
        'username' => 'before-sync',
        'sync_status' => SyncStatus::Failed,
        'last_sync_error' => 'old error',
        'last_synced_at' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->ordered('sync')
        ->andReturn([
            'username' => 'after-sync',
            'name' => 'Synced Name',
            'biography' => 'Synced bio',
            'profile_picture_url' => 'https://example.test/profile.jpg',
            'followers_count' => 5000,
            'following_count' => 350,
            'media_count' => 120,
        ]);
    $instagramGraphService->shouldReceive('retrieveMedia')
        ->once()
        ->ordered('sync');
    $instagramGraphService->shouldReceive('syncMediaInsights')
        ->once()
        ->ordered('sync');
    $instagramGraphService->shouldReceive('syncStories')
        ->once()
        ->ordered('sync');
    $instagramGraphService->shouldReceive('syncAudienceDemographics')
        ->once()
        ->ordered('sync');
    app()->bind(InstagramGraphService::class, fn () => $instagramGraphService);

    app(SyncAllInstagramData::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->username)->toBe('after-sync')
        ->and($account->sync_status)->toBe(SyncStatus::Idle)
        ->and($account->last_sync_error)->toBeNull()
        ->and($account->last_synced_at)->not->toBeNull();
});

it('marks account as failed when the chained sync workflow throws', function (): void {
    $account = InstagramAccount::factory()->create([
        'sync_status' => SyncStatus::Idle,
        'last_sync_error' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andReturn([
            'username' => 'after-sync',
            'name' => 'Synced Name',
            'biography' => 'Synced bio',
            'profile_picture_url' => 'https://example.test/profile.jpg',
            'followers_count' => 5000,
            'following_count' => 350,
            'media_count' => 120,
        ]);
    $instagramGraphService->shouldReceive('retrieveMedia')->once();
    $instagramGraphService->shouldReceive('syncMediaInsights')
        ->once()
        ->andThrow(new InstagramApiException('Insights sync failed'));
    $instagramGraphService->shouldNotReceive('syncStories');
    $instagramGraphService->shouldNotReceive('syncAudienceDemographics');
    app()->bind(InstagramGraphService::class, fn () => $instagramGraphService);

    app(SyncAllInstagramData::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toBe('Insights sync failed');
});

it('configures instagram sync queue settings', function (): void {
    $account = InstagramAccount::factory()->create();

    $job = new SyncAllInstagramData($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});
