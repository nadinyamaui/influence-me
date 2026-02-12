<?php

use App\Enums\MediaType;
use App\Jobs\SyncInstagramStories;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Services\Facebook\Client as FacebookClient;

it('fetches active stories and syncs them as story media records', function (): void {
    $account = InstagramAccount::factory()->create();

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getStories')
        ->once()
        ->andReturn(collect([
            [
                'id' => 'story-1',
                'caption' => 'Story one',
                'media_type' => 'STORY',
                'media_url' => 'https://example.test/story-1.jpg',
                'thumbnail_url' => 'https://example.test/story-1-thumb.jpg',
                'permalink' => 'https://instagram.com/stories/story-1',
                'timestamp' => '2026-02-12T10:00:00+0000',
            ],
            [
                'id' => 'story-2',
                'caption' => null,
                'media_type' => 'STORY',
                'media_url' => 'https://example.test/story-2.jpg',
                'thumbnail_url' => null,
                'permalink' => 'https://instagram.com/stories/story-2',
                'timestamp' => '2026-02-12T10:15:00+0000',
            ],
        ]));
    app()->bind(FacebookClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncInstagramStories::class, ['account' => $account])->handle();

    expect(InstagramMedia::count())->toBe(2)
        ->and(InstagramMedia::where('instagram_media_id', 'story-1')->first()->media_type)->toBe(MediaType::Story)
        ->and(InstagramMedia::where('instagram_media_id', 'story-2')->first()->media_type)->toBe(MediaType::Story);
});

it('updates existing story records when rerun to keep sync idempotent', function (): void {
    $account = InstagramAccount::factory()->create();

    InstagramMedia::factory()->story()->create([
        'instagram_account_id' => $account->id,
        'instagram_media_id' => 'story-1',
        'caption' => 'Old story caption',
        'media_url' => 'https://example.test/old-story-1.jpg',
        'published_at' => now()->subDay(),
    ]);

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getStories')
        ->once()
        ->andReturn(collect([
            [
                'id' => 'story-1',
                'caption' => 'Updated story caption',
                'media_type' => 'STORY',
                'media_url' => 'https://example.test/new-story-1.jpg',
                'thumbnail_url' => null,
                'permalink' => 'https://instagram.com/stories/story-1',
                'timestamp' => '2026-02-12T11:00:00+0000',
            ],
        ]));
    app()->bind(FacebookClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncInstagramStories::class, ['account' => $account])->handle();

    expect(InstagramMedia::where('instagram_media_id', 'story-1')->count())->toBe(1);

    $story = InstagramMedia::where('instagram_media_id', 'story-1')->first();

    expect($story->caption)->toBe('Updated story caption')
        ->and($story->media_url)->toBe('https://example.test/new-story-1.jpg')
        ->and($story->media_type)->toBe(MediaType::Story);
});

it('configures queue settings for stories sync workloads', function (): void {
    $account = InstagramAccount::factory()->create();
    $job = new SyncInstagramStories($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});
