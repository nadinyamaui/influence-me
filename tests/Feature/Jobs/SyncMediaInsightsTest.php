<?php

use App\Enums\MediaType;
use App\Jobs\SyncMediaInsights;
use App\Models\SocialAccountMedia;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\Client;
use Illuminate\Support\Collection;

it('syncs insights using views for impressions on recent non-story media and calculates engagement rate', function (): void {
    $account = SocialAccount::factory()->create();

    $recentPost = SocialAccountMedia::factory()->post()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'media-recent-post',
        'published_at' => now()->subDays(5),
        'like_count' => 80,
        'comments_count' => 20,
        'saved_count' => 0,
        'shares_count' => 0,
        'reach' => 0,
        'impressions' => 0,
        'engagement_rate' => 0,
    ]);

    $recentReel = SocialAccountMedia::factory()->reel()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'media-recent-reel',
        'published_at' => now()->subDays(10),
        'like_count' => 40,
        'comments_count' => 10,
        'saved_count' => 0,
        'shares_count' => 0,
        'reach' => 0,
        'impressions' => 0,
        'engagement_rate' => 0,
    ]);

    $oldPost = SocialAccountMedia::factory()->post()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'media-old-post',
        'published_at' => now()->subDays(95),
        'reach' => 17,
        'impressions' => 22,
    ]);

    $recentStory = SocialAccountMedia::factory()->story()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'media-recent-story',
        'published_at' => now()->subDays(3),
        'reach' => 33,
        'impressions' => 44,
    ]);

    $facebookClient = \Mockery::mock(Client::class);
    $facebookClient->shouldReceive('getMediaInsights')
        ->once()
        ->with('media-recent-post', MediaType::Post)
        ->andReturn(new Collection([
            'reach' => 1000,
            'views' => 1200,
            'saved' => 20,
            'shares' => 10,
        ]));
    $facebookClient->shouldReceive('getMediaInsights')
        ->once()
        ->with('media-recent-reel', MediaType::Reel)
        ->andReturn(new Collection([
            'reach' => 200,
            'views' => 0,
            'saved' => 5,
            'shares' => 1,
        ]));

    app()->bind(Client::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncMediaInsights::class, ['account' => $account])->handle();

    $recentPost->refresh();
    $recentReel->refresh();
    $oldPost->refresh();
    $recentStory->refresh();

    expect($recentPost->reach)->toBe(1000)
        ->and($recentPost->impressions)->toBe(1200)
        ->and($recentPost->saved_count)->toBe(20)
        ->and($recentPost->shares_count)->toBe(10)
        ->and((float) $recentPost->engagement_rate)->toBe(13.0)
        ->and($recentReel->reach)->toBe(200)
        ->and($recentReel->impressions)->toBe(0)
        ->and($recentReel->saved_count)->toBe(5)
        ->and($recentReel->shares_count)->toBe(1)
        ->and((float) $recentReel->engagement_rate)->toBe(28.0)
        ->and($oldPost->reach)->toBe(17)
        ->and($oldPost->impressions)->toBe(22)
        ->and($recentStory->reach)->toBe(33)
        ->and($recentStory->impressions)->toBe(44);
});

it('maps views metric to impressions when syncing insights', function (): void {
    $account = SocialAccount::factory()->create();

    $recentPost = SocialAccountMedia::factory()->post()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'media-rate-limit',
        'published_at' => now()->subDays(2),
        'like_count' => 10,
        'comments_count' => 5,
    ]);

    $facebookClient = \Mockery::mock(Client::class);
    $facebookClient->shouldReceive('getMediaInsights')
        ->once()
        ->with('media-rate-limit', MediaType::Post)
        ->andReturn(new Collection([
            'reach' => 50,
            'views' => 60,
            'saved' => 3,
            'shares' => 2,
        ]));

    app()->bind(Client::class, fn () => $facebookClient);

    app(SyncMediaInsights::class, ['account' => $account])->handle();

    $recentPost->refresh();

    expect($recentPost->reach)->toBe(50)
        ->and($recentPost->impressions)->toBe(60)
        ->and($recentPost->saved_count)->toBe(3)
        ->and($recentPost->shares_count)->toBe(2)
        ->and((float) $recentPost->engagement_rate)->toBe(40.0);
});

it('configures queue settings for insight sync workloads', function (): void {
    $account = SocialAccount::factory()->create();
    $job = new SyncMediaInsights($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(600);
});
