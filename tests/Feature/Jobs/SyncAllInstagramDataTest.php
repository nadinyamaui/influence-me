<?php

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Jobs\SyncAllSocialMediaData;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\InstagramGraphService;

it('runs the full instagram sync workflow and marks account as idle on success', function (): void {
    $account = SocialAccount::factory()->create([
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

    app(SyncAllSocialMediaData::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->username)->toBe('after-sync')
        ->and($account->sync_status)->toBe(SyncStatus::Idle)
        ->and($account->last_sync_error)->toBeNull()
        ->and($account->last_synced_at)->not->toBeNull();
});

it('marks account as failed when the chained sync workflow throws', function (): void {
    $account = SocialAccount::factory()->create([
        'sync_status' => SyncStatus::Idle,
        'last_sync_error' => null,
        'last_synced_at' => null,
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

    expect(fn () => app(SyncAllSocialMediaData::class, ['account' => $account])->handle())
        ->toThrow(InstagramApiException::class, 'Insights sync failed');

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toBe('Insights sync failed')
        ->and($account->last_synced_at)->toBeNull();
});

it('configures instagram sync queue settings', function (): void {
    $account = SocialAccount::factory()->create();

    $job = new SyncAllSocialMediaData($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});

it('does not overwrite failed status when profile marks account as failed without throwing', function (): void {
    $account = SocialAccount::factory()->create([
        'sync_status' => SyncStatus::Idle,
        'last_sync_error' => null,
        'last_synced_at' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->ordered('sync')
        ->andThrow(new InstagramTokenExpiredException('Token expired'));
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

    app(SyncAllSocialMediaData::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toBe('Token expired')
        ->and($account->last_synced_at)->toBeNull();
});
