<?php

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Jobs\SyncSocialMediaProfile;
use App\Models\SocialAccount;
use App\Services\Instagram\InstagramGraphService;
use Illuminate\Support\Facades\Log;

it('syncs instagram profile fields to the database', function (): void {
    $account = SocialAccount::factory()->business()->create([
        'username' => 'old-username',
        'name' => 'Old Name',
        'biography' => 'Old bio',
        'profile_picture_url' => 'https://example.test/old.jpg',
        'followers_count' => 5,
        'following_count' => 10,
        'media_count' => 15,
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => 'old error',
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andReturn([
            'username' => 'new-username',
            'name' => 'New Name',
            'biography' => 'New bio',
            'profile_picture_url' => 'https://example.test/new.jpg',
            'followers_count' => 1500,
            'following_count' => 200,
            'media_count' => 120,
        ]);

    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(SyncSocialMediaProfile::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->username)->toBe('new-username')
        ->and($account->name)->toBe('New Name')
        ->and($account->biography)->toBe('New bio')
        ->and($account->profile_picture_url)->toBe('https://example.test/new.jpg')
        ->and($account->followers_count)->toBe(1500)
        ->and($account->following_count)->toBe(200)
        ->and($account->media_count)->toBe(120)
        ->and($account->sync_status)->toBe(SyncStatus::Idle)
        ->and($account->last_sync_error)->toBeNull()
        ->and($account->last_synced_at)->not->toBeNull();
});

it('marks account as failed when token is expired and does not rethrow', function (): void {
    Log::spy();

    $account = SocialAccount::factory()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andThrow(new InstagramTokenExpiredException('Token expired'));

    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(SyncSocialMediaProfile::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toBe('Token expired');

    Log::shouldHaveReceived('warning')
        ->once();
});

it('rethrows api exceptions so the queue retry policy can apply', function (): void {
    $account = SocialAccount::factory()->create();

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andThrow(new InstagramApiException('API temporarily unavailable'));

    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    expect(fn () => app(SyncSocialMediaProfile::class, ['account' => $account])->handle())
        ->toThrow(InstagramApiException::class, 'API temporarily unavailable');
});

it('configures queue and retry backoff settings', function (): void {
    $account = SocialAccount::factory()->create();

    $job = new SyncSocialMediaProfile($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([30, 60, 120]);
});

it('can update profile fields without finalizing sync state', function (): void {
    $syncedAt = now()->subDay();

    $account = SocialAccount::factory()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_synced_at' => $syncedAt,
        'last_sync_error' => 'keep until full sync completes',
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andReturn([
            'username' => 'chain-username',
            'name' => 'Chain Name',
            'biography' => 'Chain bio',
            'profile_picture_url' => 'https://example.test/chain.jpg',
            'followers_count' => 1700,
            'following_count' => 220,
            'media_count' => 95,
        ]);

    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(SyncSocialMediaProfile::class, ['account' => $account, 'finalizeSyncState' => false])->handle();

    $account->refresh();

    expect($account->username)->toBe('chain-username')
        ->and($account->sync_status)->toBe(SyncStatus::Syncing)
        ->and($account->last_synced_at?->timestamp)->toBe($syncedAt->timestamp)
        ->and($account->last_sync_error)->toBe('keep until full sync completes');
});
