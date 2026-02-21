<?php

use App\Enums\MediaType;
use App\Jobs\SyncSocialMediaStories;
use App\Models\SocialAccountMedia;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\InstagramClient;

it('fetches active stories and syncs them as story media records', function (): void {
    $account = SocialAccount::factory()->create();

    $facebookClient = \Mockery::mock(InstagramClient::class);
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
    app()->bind(InstagramClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncSocialMediaStories::class, ['account' => $account])->handle();

    expect(SocialAccountMedia::count())->toBe(2)
        ->and(SocialAccountMedia::where('social_account_media_id', 'story-1')->first()->media_type)->toBe(MediaType::Story)
        ->and(SocialAccountMedia::where('social_account_media_id', 'story-2')->first()->media_type)->toBe(MediaType::Story);
});

it('updates existing story records when rerun to keep sync idempotent', function (): void {
    $account = SocialAccount::factory()->create();

    SocialAccountMedia::factory()->story()->create([
        'social_account_id' => $account->id,
        'social_account_media_id' => 'story-1',
        'caption' => 'Old story caption',
        'media_url' => 'https://example.test/old-story-1.jpg',
        'published_at' => now()->subDay(),
    ]);

    $facebookClient = \Mockery::mock(InstagramClient::class);
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
    app()->bind(InstagramClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncSocialMediaStories::class, ['account' => $account])->handle();

    expect(SocialAccountMedia::where('social_account_media_id', 'story-1')->count())->toBe(1);

    $story = SocialAccountMedia::where('social_account_media_id', 'story-1')->first();

    expect($story->caption)->toBe('Updated story caption')
        ->and($story->media_url)->toBe('https://example.test/new-story-1.jpg')
        ->and($story->media_type)->toBe(MediaType::Story);
});

it('configures queue settings for stories sync workloads', function (): void {
    $account = SocialAccount::factory()->create();
    $job = new SyncSocialMediaStories($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});
