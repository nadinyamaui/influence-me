<?php

use App\Enums\MediaType;
use App\Jobs\SyncInstagramMedia;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Illuminate\Support\Facades\Http;

it('fetches paginated media and syncs records with mapped media types', function (): void {
    $account = InstagramAccount::factory()->create();

    Http::fake([
        'https://graph.instagram.com/v21.0/me/media*' => Http::sequence()
            ->push([
                'data' => [
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
                ],
                'paging' => [
                    'cursors' => [
                        'after' => 'cursor-2',
                    ],
                ],
            ], 200)
            ->push([
                'data' => [
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
                ],
            ], 200),
    ]);

    app(SyncInstagramMedia::class, ['account' => $account])->handle();

    expect(InstagramMedia::count())->toBe(4)
        ->and(InstagramMedia::where('instagram_media_id', 'media-1')->first()->media_type)->toBe(MediaType::Post)
        ->and(InstagramMedia::where('instagram_media_id', 'media-2')->first()->media_type)->toBe(MediaType::Reel)
        ->and(InstagramMedia::where('instagram_media_id', 'media-3')->first()->media_type)->toBe(MediaType::Post)
        ->and(InstagramMedia::where('instagram_media_id', 'media-4')->first()->media_type)->toBe(MediaType::Post);

    Http::assertSentCount(2);
});

it('updates existing media records when rerun to keep sync idempotent', function (): void {
    $account = InstagramAccount::factory()->create();

    InstagramMedia::factory()->create([
        'instagram_account_id' => $account->id,
        'instagram_media_id' => 'media-1',
        'caption' => 'Old caption',
        'media_type' => MediaType::Post,
        'like_count' => 1,
        'comments_count' => 0,
    ]);

    Http::fake([
        'https://graph.instagram.com/v21.0/me/media*' => Http::response([
            'data' => [
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
            ],
        ], 200),
    ]);

    app(SyncInstagramMedia::class, ['account' => $account])->handle();

    expect(InstagramMedia::where('instagram_media_id', 'media-1')->count())->toBe(1);

    $media = InstagramMedia::where('instagram_media_id', 'media-1')->first();

    expect($media->caption)->toBe('Updated caption')
        ->and($media->like_count)->toBe(99)
        ->and($media->comments_count)->toBe(22);
});

it('configures queue settings for larger sync workloads', function (): void {
    $account = InstagramAccount::factory()->create();
    $job = new SyncInstagramMedia($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(300);
});
