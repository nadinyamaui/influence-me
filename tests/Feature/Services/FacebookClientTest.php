<?php

use App\Enums\MediaType;
use App\Services\Facebook\Client;
use FacebookAds\Api;
use FacebookAds\Http\ResponseInterface;
use FacebookAds\Object\IGMedia;
use FacebookAds\Object\IGUser;
use FacebookAds\Object\InstagramInsightsResult;
use FacebookAds\Object\Page;
use FacebookAds\Object\User;

it('initializes facebook api with configured credentials and default graph version', function (): void {
    config()->set('services.facebook.client_id', 'facebook-client-id');
    config()->set('services.facebook.client_secret', 'facebook-client-secret');

    $client = new Client('short-lived-token');

    $clientReflection = new ReflectionClass(Client::class);
    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $api = $apiProperty->getValue($client);

    expect($api)->toBeInstanceOf(Api::class)
        ->and($api->getDefaultGraphVersion())->toBe('24.0');
});

it('gets a long lived token from the facebook oauth endpoint', function (): void {
    config()->set('services.facebook.client_id', 'facebook-client-id');
    config()->set('services.facebook.client_secret', 'facebook-client-secret');

    $tokenResponse = [
        'access_token' => 'long-lived-token',
        'token_type' => 'bearer',
        'expires_in' => 5183944,
    ];

    $response = \Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->once()
        ->andReturn($tokenResponse);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/oauth/access_token'
                && $method === 'GET'
                && $params === [
                    'client_id' => 'facebook-client-id',
                    'client_secret' => 'facebook-client-secret',
                    'grant_type' => 'fb_exchange_token',
                    'fb_exchange_token' => 'short-lived-token',
                ];
        })
        ->andReturn($response);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $accessTokenProperty = $clientReflection->getProperty('access_token');
    $accessTokenProperty->setAccessible(true);
    $accessTokenProperty->setValue($client, 'short-lived-token');

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->getLongLivedToken())->toBe($tokenResponse);
});

it('refreshes a long lived token from the facebook oauth endpoint', function (): void {
    config()->set('services.facebook.client_id', 'facebook-client-id');
    config()->set('services.facebook.client_secret', 'facebook-client-secret');

    $tokenResponse = [
        'access_token' => 'refreshed-long-lived-token',
        'token_type' => 'bearer',
        'expires_in' => 5183944,
    ];

    $response = \Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->once()
        ->andReturn($tokenResponse);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/oauth/access_token'
                && $method === 'GET'
                && $params === [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => 'facebook-client-id',
                    'client_secret' => 'facebook-client-secret',
                    'fb_exchange_token' => 'existing-long-lived-token',
                ];
        })
        ->andReturn($response);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $accessTokenProperty = $clientReflection->getProperty('access_token');
    $accessTokenProperty->setAccessible(true);
    $accessTokenProperty->setValue($client, 'existing-long-lived-token');

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->refreshLongLivedToken())->toBe($tokenResponse);
});

it('returns mapped instagram accounts from the facebook accounts endpoint', function (): void {
    $pageWithSocialAccount = \Mockery::mock(Page::class);
    $pageWithSocialAccount->shouldReceive('getData')
        ->times(2)
        ->andReturn([
            'id' => 'page-1',
            'name' => 'Creator Page',
            'access_token' => 'page-token-1',
            'instagram_business_account' => [
                'id' => 'ig-1',
                'username' => 'creator.one',
                'name' => 'Creator One',
                'biography' => 'Creator bio',
                'profile_picture_url' => 'https://example.com/pic.jpg',
                'followers_count' => 1200,
                'follows_count' => 450,
                'media_count' => 88,
            ],
        ]);

    $pageWithoutSocialAccount = \Mockery::mock(Page::class);
    $pageWithoutSocialAccount->shouldReceive('getData')
        ->once()
        ->andReturn([
            'id' => 'page-2',
            'name' => 'No Instagram Page',
            'access_token' => 'page-token-2',
        ]);

    $facebookUser = \Mockery::mock('overload:'.User::class);
    $facebookUser->shouldReceive('getAccounts')
        ->once()
        ->with([
            'id',
            'name',
            'access_token',
            'category',
            'followers_count',
            'verification_status',
            'instagram_business_account{id,username,name,biography,profile_picture_url,followers_count,follows_count,media_count}',
        ])
        ->andReturn(new ArrayObject([$pageWithSocialAccount, $pageWithoutSocialAccount]));

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->accounts()->all())->toBe([
        [
            'social_network' => 'instagram',
            'social_network_user_id' => 'ig-1',
            'name' => 'Creator One',
            'username' => 'creator.one',
            'biography' => 'Creator bio',
            'profile_picture_url' => 'https://example.com/pic.jpg',
            'followers_count' => 1200,
            'following_count' => 450,
            'media_count' => 88,
            'access_token' => 'page-token-1',
        ],
    ]);
});

it('filters out accounts without instagram id and normalizes biography', function (): void {
    $pageWithInstagramId = \Mockery::mock(Page::class);
    $pageWithInstagramId->shouldReceive('getData')
        ->times(2)
        ->andReturn([
            'id' => 'page-1',
            'name' => 'Creator Page',
            'access_token' => 'page-token-1',
            'instagram_business_account' => [
                'id' => 'ig-1',
                'username' => 'creator.one',
                'name' => 'Creator One',
                'biography' => '   padded bio   ',
                'profile_picture_url' => 'https://example.com/pic.jpg',
                'followers_count' => 1200,
                'follows_count' => 450,
                'media_count' => 88,
            ],
        ]);

    $pageWithoutInstagramId = \Mockery::mock(Page::class);
    $pageWithoutInstagramId->shouldReceive('getData')
        ->once()
        ->andReturn([
            'id' => 'page-2',
            'name' => 'No Instagram Id',
            'access_token' => 'page-token-2',
            'instagram_business_account' => [
                'username' => 'missing.id',
                'name' => 'Missing ID',
                'profile_picture_url' => 'https://example.com/missing.jpg',
                'followers_count' => 10,
                'follows_count' => 20,
                'media_count' => 30,
            ],
        ]);

    $pageWithNullInstagramId = \Mockery::mock(Page::class);
    $pageWithNullInstagramId->shouldReceive('getData')
        ->once()
        ->andReturn([
            'id' => 'page-3',
            'name' => 'Null Biography',
            'access_token' => 'page-token-3',
            'instagram_business_account' => [
                'id' => null,
                'username' => 'null.id',
                'name' => 'Null ID',
                'profile_picture_url' => 'https://example.com/null.jpg',
                'followers_count' => 10,
                'follows_count' => 20,
                'media_count' => 30,
                'biography' => null,
            ],
        ]);

    $facebookUser = \Mockery::mock('overload:'.User::class);
    $facebookUser->shouldReceive('getAccounts')
        ->once()
        ->with([
            'id',
            'name',
            'access_token',
            'category',
            'followers_count',
            'verification_status',
            'instagram_business_account{id,username,name,biography,profile_picture_url,followers_count,follows_count,media_count}',
        ])
        ->andReturn(new ArrayObject([$pageWithInstagramId, $pageWithoutInstagramId, $pageWithNullInstagramId]));

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->accounts()->all())->toBe([
        [
            'social_network' => 'instagram',
            'social_network_user_id' => 'ig-1',
            'name' => 'Creator One',
            'username' => 'creator.one',
            'biography' => 'padded bio',
            'profile_picture_url' => 'https://example.com/pic.jpg',
            'followers_count' => 1200,
            'following_count' => 450,
            'media_count' => 88,
            'access_token' => 'page-token-1',
        ],
    ]);
});

it('gets all instagram media from graph endpoint', function (): void {
    $mediaOne = new class
    {
        public function exportAllData(): array
        {
            return ['id' => 'media-1', 'media_type' => 'IMAGE'];
        }
    };

    $mediaTwo = new class
    {
        public function exportAllData(): array
        {
            return ['id' => 'media-2', 'media_type' => 'VIDEO'];
        }
    };

    $cursor = new class([$mediaOne, $mediaTwo]) implements IteratorAggregate
    {
        public function __construct(private array $items) {}

        public function setUseImplicitFetch(bool $useImplicitFetch): void {}

        public function getIterator(): Traversable
        {
            return new ArrayIterator($this->items);
        }
    };

    $igUser = \Mockery::mock('overload:'.IGUser::class);
    $igUser->shouldReceive('getMedia')
        ->once()
        ->with([
            'id',
            'caption',
            'media_type',
            'media_product_type',
            'media_url',
            'thumbnail_url',
            'permalink',
            'timestamp',
            'like_count',
            'comments_count',
        ])
        ->andReturn($cursor);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->getAllMedia())->toBe([
        ['id' => 'media-1', 'media_type' => 'IMAGE'],
        ['id' => 'media-2', 'media_type' => 'VIDEO'],
    ]);
});

it('returns an empty array when no instagram media is available', function (): void {
    $cursor = new class([]) implements IteratorAggregate
    {
        public function __construct(private array $items) {}

        public function setUseImplicitFetch(bool $useImplicitFetch): void {}

        public function getIterator(): Traversable
        {
            return new ArrayIterator($this->items);
        }
    };

    $igUser = \Mockery::mock('overload:'.IGUser::class);
    $igUser->shouldReceive('getMedia')
        ->once()
        ->andReturn($cursor);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->getAllMedia())->toBe([]);
});

it('returns empty story collection when graph endpoint has no media', function (): void {
    $cursor = new class
    {
        public function getArrayCopy(): array
        {
            return [];
        }
    };

    $igUser = \Mockery::mock('overload:'.IGUser::class);
    $igUser->shouldReceive('getStories')
        ->once()
        ->with([
            'id',
            'caption',
            'media_type',
            'media_url',
            'thumbnail_url',
            'permalink',
            'timestamp',
        ], [
            'limit' => 100,
        ])
        ->andReturn($cursor);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    $stories = $client->getStories();

    expect($stories)->toHaveCount(0);
});

it('gets a single instagram media item from graph endpoint', function (): void {
    $mediaResponse = [
        'id' => '17900000000000001',
        'caption' => 'Single media caption',
        'media_type' => 'IMAGE',
        'media_url' => 'https://example.com/media.jpg',
        'thumbnail_url' => 'https://example.com/thumb.jpg',
        'permalink' => 'https://instagram.com/p/example',
        'timestamp' => '2026-02-12T12:00:00+0000',
        'like_count' => 34,
        'comments_count' => 8,
    ];

    $media = new class($mediaResponse)
    {
        public function __construct(private array $data) {}

        public function exportAllData(): array
        {
            return $this->data;
        }
    };

    $igMedia = \Mockery::mock('overload:'.IGMedia::class);
    $igMedia->shouldReceive('getSelf')
        ->once()
        ->with([
            'id',
            'caption',
            'media_type',
            'media_url',
            'thumbnail_url',
            'permalink',
            'timestamp',
            'like_count',
            'comments_count',
        ])
        ->andReturn($media);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    expect($client->getMedia(17900000000000001))->toBe($mediaResponse);
});

it('returns nullable thumbnail_url when single instagram media response omits value', function (): void {
    $media = new class
    {
        public function exportAllData(): array
        {
            return [
                'id' => '17900000000000001',
                'caption' => 'Single media caption',
                'media_type' => 'IMAGE',
                'media_url' => 'https://example.com/media.jpg',
                'permalink' => 'https://instagram.com/p/example',
                'timestamp' => '2026-02-12T12:00:00+0000',
                'like_count' => 34,
                'comments_count' => 8,
            ];
        }
    };

    $igMedia = \Mockery::mock('overload:'.IGMedia::class);
    $igMedia->shouldReceive('getSelf')
        ->once()
        ->andReturn($media);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    expect($client->getMedia(17900000000000001))->toBe([
        'id' => '17900000000000001',
        'caption' => 'Single media caption',
        'media_type' => 'IMAGE',
        'media_url' => 'https://example.com/media.jpg',
        'thumbnail_url' => null,
        'permalink' => 'https://instagram.com/p/example',
        'timestamp' => '2026-02-12T12:00:00+0000',
        'like_count' => 34,
        'comments_count' => 8,
    ]);
});

it('gets image and carousel media insights from graph endpoint', function (): void {
    $insights = [
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
    ];

    $insights[0]->shouldReceive('getData')->twice()->andReturn(['name' => 'reach', 'values' => [['value' => 90]]]);
    $insights[1]->shouldReceive('getData')->twice()->andReturn(['name' => 'likes', 'values' => [['value' => 55]]]);
    $insights[2]->shouldReceive('getData')->twice()->andReturn(['name' => 'comments', 'values' => [['value' => 7]]]);
    $insights[3]->shouldReceive('getData')->twice()->andReturn(['name' => 'shares', 'values' => [['value' => 3]]]);
    $insights[4]->shouldReceive('getData')->twice()->andReturn(['name' => 'saved', 'values' => [['value' => 12]]]);
    $insights[5]->shouldReceive('getData')->twice()->andReturn(['name' => 'total_interactions', 'values' => [['value' => 80]]]);

    $igMedia = \Mockery::mock('overload:'.IGMedia::class);
    $igMedia->shouldReceive('getInsights')
        ->once()
        ->withArgs(function (array $params): bool {
            return ($params['metric'] ?? null) === MediaType::Post->metrics();
        })
        ->andReturn(new ArrayObject($insights));

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    expect($client->getMediaInsights('17900000000000001', MediaType::Post)->all())->toBe([
        'reach' => 90,
        'likes' => 55,
        'comments' => 7,
        'shares' => 3,
        'saved' => 12,
        'total_interactions' => 80,
    ]);
});

it('gets video and reel media insights from graph endpoint', function (): void {
    $insights = [
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
    ];

    $insights[0]->shouldReceive('getData')->twice()->andReturn(['name' => 'views', 'values' => [['value' => 500]]]);
    $insights[1]->shouldReceive('getData')->twice()->andReturn(['name' => 'plays', 'values' => [['value' => 400]]]);
    $insights[2]->shouldReceive('getData')->twice()->andReturn(['name' => 'reach', 'values' => [['value' => 101]]]);
    $insights[3]->shouldReceive('getData')->twice()->andReturn(['name' => 'total_interactions', 'values' => [['value' => 90]]]);
    $insights[4]->shouldReceive('getData')->twice()->andReturn(['name' => 'ig_reels_avg_watch_time', 'values' => [['value' => 3.2]]]);
    $insights[5]->shouldReceive('getData')->twice()->andReturn(['name' => 'ig_reels_video_view_total_time', 'values' => [['value' => 1280]]]);
    $insights[6]->shouldReceive('getData')->twice()->andReturn(['name' => 'clips_replays_count', 'values' => [['value' => 41]]]);
    $insights[7]->shouldReceive('getData')->twice()->andReturn(['name' => 'reels_skip_rate', 'values' => [['value' => 12.5]]]);

    $igMedia = \Mockery::mock('overload:'.IGMedia::class);
    $igMedia->shouldReceive('getInsights')
        ->once()
        ->withArgs(function (array $params): bool {
            return ($params['metric'] ?? null) === MediaType::Reel->metrics();
        })
        ->andReturn(new ArrayObject($insights));

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    expect($client->getMediaInsights('17900000000000002', MediaType::Reel)->all())->toBe([
        'views' => 500,
        'plays' => 400,
        'reach' => 101,
        'total_interactions' => 90,
        'ig_reels_avg_watch_time' => 3.2,
        'ig_reels_video_view_total_time' => 1280,
        'clips_replays_count' => 41,
        'reels_skip_rate' => 12.5,
    ]);
});

it('gets story media insights from graph endpoint', function (): void {
    $insights = [
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
        \Mockery::mock(InstagramInsightsResult::class),
    ];

    $insights[0]->shouldReceive('getData')->twice()->andReturn(['name' => 'reach', 'values' => [['value' => 39]]]);
    $insights[1]->shouldReceive('getData')->twice()->andReturn(['name' => 'replies', 'values' => [['value' => 4]]]);
    $insights[2]->shouldReceive('getData')->twice()->andReturn(['name' => 'navigation', 'values' => [['value' => ['tap_forward' => 10, 'tap_back' => 5]]]]);

    $igMedia = \Mockery::mock('overload:'.IGMedia::class);
    $igMedia->shouldReceive('getInsights')
        ->once()
        ->withArgs(function (array $params): bool {
            return ($params['metric'] ?? null) === MediaType::Story->metrics();
        })
        ->andReturn(new ArrayObject($insights));

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    expect($client->getMediaInsights('17900000000000003', MediaType::Story)->all())->toBe([
        'reach' => 39,
        'replies' => 4,
        'navigation' => ['tap_forward' => 10, 'tap_back' => 5],
    ]);
});

it('gets instagram profile data from graph endpoint', function (): void {
    $profile = new class
    {
        public function exportAllData(): array
        {
            return [
                'id' => '17841405822304914',
                'username' => 'creator.one',
                'name' => 'Creator One',
                'biography' => 'Creator bio',
                'profile_picture_url' => 'https://example.com/profile.jpg',
                'followers_count' => 1200,
                'follows_count' => 450,
                'media_count' => 88,
            ];
        }
    };

    $igUser = \Mockery::mock('overload:'.IGUser::class);
    $igUser->shouldReceive('getSelf')
        ->once()
        ->with([
            'id',
            'username',
            'name',
            'biography',
            'profile_picture_url',
            'followers_count',
            'follows_count',
            'media_count',
        ])
        ->andReturn($profile);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->getProfile())->toBe([
        'id' => '17841405822304914',
        'username' => 'creator.one',
        'name' => 'Creator One',
        'biography' => 'Creator bio',
        'profile_picture_url' => 'https://example.com/profile.jpg',
        'followers_count' => 1200,
        'following_count' => 450,
        'media_count' => 88,
    ]);
});

it('returns nullable instagram profile keys when graph response omits values', function (): void {
    $profile = new class
    {
        public function exportAllData(): array
        {
            return [
                'id' => '17841405822304914',
                'username' => 'creator.one',
                'name' => null,
                'biography' => null,
                'profile_picture_url' => null,
                'followers_count' => null,
                'follows_count' => null,
                'media_count' => null,
            ];
        }
    };

    $igUser = \Mockery::mock('overload:'.IGUser::class);
    $igUser->shouldReceive('getSelf')
        ->once()
        ->andReturn($profile);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->getProfile())->toBe([
        'id' => '17841405822304914',
        'username' => 'creator.one',
        'name' => null,
        'biography' => null,
        'profile_picture_url' => null,
        'followers_count' => 0,
        'following_count' => 0,
        'media_count' => 0,
    ]);
});
