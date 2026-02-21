<?php

use App\Enums\SyncStatus;
use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Jobs\RefreshSocialMediaToken;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\InstagramGraphService;
use Illuminate\Support\Facades\Log;

it('refreshes instagram token and stores new token expiration', function (): void {
    Log::spy();

    $account = SocialAccount::factory()->create([
        'access_token' => 'old-token',
        'token_expires_at' => now()->addDays(3),
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => 'old sync error',
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('refreshLongLivedToken')
        ->once()
        ->andReturn('new-refreshed-token');
    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(RefreshSocialMediaToken::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->access_token)->toBe('new-refreshed-token')
        ->and($account->token_expires_at)->not->toBeNull()
        ->and($account->token_expires_at->gt(now()->addDays(59)))->toBeTrue()
        ->and($account->token_expires_at->lt(now()->addDays(61)))->toBeTrue()
        ->and($account->sync_status)->toBe(SyncStatus::Idle)
        ->and($account->last_sync_error)->toBeNull();

    Log::shouldHaveReceived('info')->once();
});

it('marks account as failed when token is already expired', function (): void {
    Log::spy();

    $account = SocialAccount::factory()->tokenExpired()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldNotReceive('refreshLongLivedToken');
    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(RefreshSocialMediaToken::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toContain('requires account re-authentication');

    Log::shouldHaveReceived('warning')->once();
});

it('marks account as failed when refresh fails due to expired token response', function (): void {
    Log::spy();

    $account = SocialAccount::factory()->create([
        'sync_status' => SyncStatus::Syncing,
        'last_sync_error' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('refreshLongLivedToken')
        ->once()
        ->andThrow(new InstagramTokenExpiredException('Token expired upstream'));
    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    app(RefreshSocialMediaToken::class, ['account' => $account])->handle();

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Failed)
        ->and($account->last_sync_error)->toContain('requires account re-authentication');

    Log::shouldHaveReceived('warning')->once();
});

it('records last sync error and rethrows api failures for retry handling', function (): void {
    Log::spy();

    $account = SocialAccount::factory()->create([
        'last_sync_error' => null,
    ]);

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('refreshLongLivedToken')
        ->once()
        ->andThrow(new InstagramApiException('Facebook API unavailable'));
    app()->bind(InstagramGraphService::class, fn ($app, $parameters) => $instagramGraphService);

    expect(fn () => app(RefreshSocialMediaToken::class, ['account' => $account])->handle())
        ->toThrow(InstagramApiException::class, 'Facebook API unavailable');

    $account->refresh();

    expect($account->last_sync_error)->toBe('Facebook API unavailable');

    Log::shouldHaveReceived('error')->once();
});

it('configures queue and retry backoff settings', function (): void {
    $account = SocialAccount::factory()->create();

    $job = new RefreshSocialMediaToken($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([60, 300, 900]);
});
