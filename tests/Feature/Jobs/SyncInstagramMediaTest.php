<?php

use App\Enums\MediaType;
use App\Jobs\SyncSocialMediaMedia;
use App\Models\SocialAccountMedia;
use App\Models\SocialAccount;
use App\Services\Facebook\Client as FacebookClient;

it('fetches paginated media and syncs records with mapped media types', function (): void {
    $account = SocialAccount::factory()->create();

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getAllMedia')
        ->once()
        ->andReturn([
            [
                'id' => 'media-1',
                'caption' => 'Image post',
                'media_type' => 'IMAGE',
                'media_product_type' => 'FEED',
                'media_url' => 'https://example.test/media-1.jpg',
                'thumbnail_url' => null,
                'permalink' => 'https://instagram.com/p/media-1',
                'timestamp' => '2026-01-01T08:30:00+0000',
                'like_count' => 12,
                'comments_count' => 3,
            ],
            [
                'id' => 'media-2',
                'caption' => 'Reel video',
                'media_type' => 'VIDEO',
                'media_product_type' => 'REELS',
                'media_url' => 'https://example.test/media-2.mp4',
                'thumbnail_url' => 'https://example.test/media-2-thumb.jpg',
                'permalink' => 'https://instagram.com/reel/media-2',
                'timestamp' => '2026-01-02T08:30:00+0000',
                'like_count' => 30,
                'comments_count' => 8,
            ],
            [
                'id' => 'media-3',
                'caption' => 'Feed video',
                'media_type' => 'VIDEO',
                'media_product_type' => 'FEED',
                'media_url' => 'https://example.test/media-3.mp4',
                'thumbnail_url' => 'https://example.test/media-3-thumb.jpg',
                'permalink' => 'https://instagram.com/p/media-3',
                'timestamp' => '2026-01-03T08:30:00+0000',
                'like_count' => 5,
                'comments_count' => 1,
            ],
            [
                'id' => 'media-4',
                'caption' => 'Carousel',
                'media_type' => 'CAROUSEL_ALBUM',
                'media_product_type' => 'FEED',
                'media_url' => 'https://example.test/media-4.jpg',
                'thumbnail_url' => null,
                'permalink' => 'https://instagram.com/p/media-4',
                'timestamp' => '2026-01-04T08:30:00+0000',
                'like_count' => 7,
                'comments_count' => 2,
            ],
        ]);
    app()->bind(FacebookClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncSocialMediaMedia::class, ['account' => $account])->handle();

    expect(SocialAccountMedia::count())->toBe(4)
        ->and(SocialAccountMedia::where('social_account_media_id', 'media-1')->first()->media_type)->toBe(MediaType::Post)
        ->and(SocialAccountMedia::where('social_account_media_id', 'media-2')->first()->media_type)->toBe(MediaType::Reel)
        ->and(SocialAccountMedia::where('social_account_media_id', 'media-3')->first()->media_type)->toBe(MediaType::Post)
        ->and(SocialAccountMedia::where('social_account_media_id', 'media-4')->first()->media_type)->toBe(MediaType::Post);

});

it('updates existing media records when rerun to keep sync idempotent', function (): void {
    $account = SocialAccount::factory()->create();

    SocialAccountMedia::factory()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'media-1',
        'caption' => 'Old caption',
        'media_type' => MediaType::Post,
        'like_count' => 1,
        'comments_count' => 0,
    ]);

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getAllMedia')
        ->once()
        ->andReturn([
            [
                'id' => 'media-1',
                'caption' => 'Updated caption',
                'media_type' => 'IMAGE',
                'media_product_type' => 'FEED',
                'media_url' => 'https://example.test/media-1.jpg',
                'thumbnail_url' => null,
                'permalink' => 'https://instagram.com/p/media-1',
                'timestamp' => '2026-01-05T08:30:00+0000',
                'like_count' => 99,
                'comments_count' => 22,
            ],
        ]);
    app()->bind(FacebookClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncSocialMediaMedia::class, ['account' => $account])->handle();

    expect(SocialAccountMedia::where('social_account_media_id', 'media-1')->count())->toBe(1);

    $media = SocialAccountMedia::where('social_account_media_id', 'media-1')->first();

    expect($media->caption)->toBe('Updated caption')
        ->and($media->like_count)->toBe(99)
        ->and($media->comments_count)->toBe(22);
});

it('configures queue settings for larger sync workloads', function (): void {
    $account = SocialAccount::factory()->create();
    $job = new SyncSocialMediaMedia($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(300);
});
