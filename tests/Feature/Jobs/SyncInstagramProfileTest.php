<?php

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Jobs\SyncInstagramProfile;
use App\Models\InstagramAccount;
use App\Services\Facebook\InstagramGraphService;
use Illuminate\Support\Facades\Log;

it('syncs instagram profile fields to the database', function (): void {
    $account = InstagramAccount::factory()->business()->create([
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

    app(SyncInstagramProfile::class, ['account' => $account])->handle();

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

    $account = InstagramAccount::factory()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andThrow(new InstagramTokenExpiredException('Token expired'));

    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(SyncInstagramProfile::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toBe('Token expired');

    Log::shouldHaveReceived('warning')
        ->once();
});

it('rethrows api exceptions so the queue retry policy can apply', function (): void {
    $account = InstagramAccount::factory()->create();

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('getProfile')
        ->once()
        ->andThrow(new InstagramApiException('API temporarily unavailable'));

    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    expect(fn () => app(SyncInstagramProfile::class, ['account' => $account])->handle())
        ->toThrow(InstagramApiException::class, 'API temporarily unavailable');
});

it('configures queue and retry backoff settings', function (): void {
    $account = InstagramAccount::factory()->create();

    $job = new SyncInstagramProfile($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([30, 60, 120]);
});
