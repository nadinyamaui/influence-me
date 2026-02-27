<?php

use App\Enums\SocialNetwork;
use App\Services\SocialMedia\Tiktok\Client;
use App\Services\SocialMedia\Tiktok\TikTokApiConnector;

it('maps tiktok user info payload to social account attributes', function (): void {
    $connector = \Mockery::mock(TikTokApiConnector::class);
    $connector->shouldReceive('get')
        ->once()
        ->with('/v2/user/info/', [
            'fields' => 'open_id,display_name,avatar_url,bio_description,follower_count,following_count,video_count',
        ])
        ->andReturn([
            'user' => [
                'open_id' => 'tt-open-1',
                'display_name' => 'creator_name',
                'avatar_url' => 'https://example.test/avatar.jpg',
                'bio_description' => 'Creator bio',
                'follower_count' => 451,
                'following_count' => 89,
                'video_count' => 24,
            ],
        ]);

    app()->bind(TikTokApiConnector::class, fn (): TikTokApiConnector => $connector);

    $account = (new Client(access_token: 'token-123'))->accounts()->first();

    expect($account)->toBeArray()
        ->and($account['social_network'])->toBe(SocialNetwork::Tiktok->value)
        ->and($account['social_network_user_id'])->toBe('tt-open-1')
        ->and($account['username'])->toBe('creator_name')
        ->and($account['followers_count'])->toBe(451)
        ->and($account['media_count'])->toBe(24)
        ->and($account['access_token'])->toBe('token-123');
});

it('returns no accounts when tiktok response has no identifier', function (): void {
    $connector = \Mockery::mock(TikTokApiConnector::class);
    $connector->shouldReceive('get')
        ->once()
        ->andReturn([
            'user' => [
                'display_name' => 'creator_name',
            ],
        ]);

    app()->bind(TikTokApiConnector::class, fn (): TikTokApiConnector => $connector);

    $accounts = (new Client(access_token: 'token-123'))->accounts();

    expect($accounts)->toHaveCount(0);
});

it('retrieves all tiktok media across paginated responses', function (): void {
    $connector = \Mockery::mock(TikTokApiConnector::class);
    $connector->shouldReceive('request')
        ->once()
        ->with('POST', '/v2/video/list/', [
            'query' => [
                'fields' => 'id,title,video_description,duration,cover_image_url,embed_link,share_url,like_count,comment_count,share_count,view_count,create_time',
            ],
            'json' => [
                'max_count' => 20,
                'cursor' => 0,
            ],
        ])
        ->andReturn([
            'videos' => [
                ['id' => 'v1', 'like_count' => 12],
                ['id' => 'v2', 'like_count' => 3],
            ],
            'has_more' => true,
            'cursor' => 20,
        ]);
    $connector->shouldReceive('request')
        ->once()
        ->with('POST', '/v2/video/list/', [
            'query' => [
                'fields' => 'id,title,video_description,duration,cover_image_url,embed_link,share_url,like_count,comment_count,share_count,view_count,create_time',
            ],
            'json' => [
                'max_count' => 20,
                'cursor' => 20,
            ],
        ])
        ->andReturn([
            'videos' => [
                ['id' => 'v3', 'like_count' => 19],
            ],
            'has_more' => false,
            'cursor' => 40,
        ]);

    app()->bind(TikTokApiConnector::class, fn (): TikTokApiConnector => $connector);

    $videos = (new Client(access_token: 'token-123'))->getAllMedia();

    expect($videos)->toHaveCount(3)
        ->and($videos->pluck('id')->all())->toBe(['v1', 'v2', 'v3']);
});

it('retrieves per-post stats for tiktok media ids', function (): void {
    $connector = \Mockery::mock(TikTokApiConnector::class);
    $connector->shouldReceive('request')
        ->once()
        ->with('POST', '/v2/video/query/', [
            'query' => [
                'fields' => 'id,like_count,comment_count,share_count,view_count,create_time,title',
            ],
            'json' => [
                'filters' => [
                    'video_ids' => ['v1', 'v2'],
                ],
            ],
        ])
        ->andReturn([
            'videos' => [
                [
                    'id' => 'v1',
                    'like_count' => 100,
                    'comment_count' => 5,
                    'share_count' => 8,
                    'view_count' => 4200,
                    'create_time' => 1700000000,
                    'title' => 'First video',
                ],
                [
                    'id' => 'v2',
                    'like_count' => 50,
                    'comment_count' => 2,
                    'share_count' => 3,
                    'view_count' => 1800,
                    'create_time' => 1700000050,
                    'title' => 'Second video',
                ],
            ],
        ]);

    app()->bind(TikTokApiConnector::class, fn (): TikTokApiConnector => $connector);

    $stats = (new Client(access_token: 'token-123'))->getMediaStats(collect(['v1', 'v2']));

    expect($stats->keys()->all())->toBe(['v1', 'v2'])
        ->and($stats['v1']['view_count'])->toBe(4200)
        ->and($stats['v2']['like_count'])->toBe(50);
});
