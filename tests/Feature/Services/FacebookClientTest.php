<?php

use App\Services\Facebook\Client;
use FacebookAds\Api;
use FacebookAds\Http\ResponseInterface;
use FacebookAds\Object\IGUser;
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

it('returns mapped instagram accounts from the facebook accounts endpoint', function (): void {
    $pageWithInstagramAccount = \Mockery::mock(Page::class);
    $pageWithInstagramAccount->shouldReceive('getData')
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

    $pageWithoutInstagramAccount = \Mockery::mock(Page::class);
    $pageWithoutInstagramAccount->shouldReceive('getData')
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
        ->andReturn(new ArrayObject([$pageWithInstagramAccount, $pageWithoutInstagramAccount]));

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $userIdProperty = $clientReflection->getProperty('user_id');
    $userIdProperty->setAccessible(true);
    $userIdProperty->setValue($client, '1234567890');

    expect($client->accounts()->all())->toBe([
        [
            'instagram_user_id' => 'ig-1',
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
            'instagram_user_id' => 'ig-1',
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

    $response = \Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->once()
        ->andReturn($mediaResponse);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000001'
                && $method === 'GET'
                && $params === [
                    'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
                ];
        })
        ->andReturn($response);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->getMedia(17900000000000001))->toBe($mediaResponse);
});

it('returns nullable media keys when single instagram media response omits values', function (): void {
    $response = \Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'id' => '17900000000000001',
        ]);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->andReturn($response);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->getMedia(17900000000000001))->toBe([
        'id' => '17900000000000001',
        'caption' => null,
        'media_type' => null,
        'media_url' => null,
        'thumbnail_url' => null,
        'permalink' => null,
        'timestamp' => null,
        'like_count' => null,
        'comments_count' => null,
    ]);
});

it('gets image and carousel media insights from graph endpoint', function (): void {
    $mediaTypeResponse = \Mockery::mock(ResponseInterface::class);
    $mediaTypeResponse->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'media_type' => 'IMAGE',
        ]);

    $insightsResponse = \Mockery::mock(ResponseInterface::class);
    $insightsResponse->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'data' => [
                ['name' => 'impressions', 'values' => [['value' => 100]]],
                ['name' => 'reach', 'values' => [['value' => 90]]],
                ['name' => 'saved', 'values' => [['value' => 12]]],
                ['name' => 'engagement', 'values' => [['value' => 80]]],
                ['name' => 'likes', 'values' => [['value' => 55]]],
                ['name' => 'comments', 'values' => [['value' => 7]]],
                ['name' => 'shares', 'values' => [['value' => 3]]],
            ],
        ]);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000001'
                && $method === 'GET'
                && $params === [
                    'fields' => 'media_type',
                ];
        })
        ->andReturn($mediaTypeResponse);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000001/insights'
                && $method === 'GET'
                && $params === [
                    'metric' => 'impressions,reach,saved,engagement,likes,comments,shares',
                ];
        })
        ->andReturn($insightsResponse);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->getMediaInsights('17900000000000001'))->toBe([
        'impressions' => 100,
        'reach' => 90,
        'saved' => 12,
        'engagement' => 80,
        'likes' => 55,
        'comments' => 7,
        'shares' => 3,
    ]);
});

it('gets video and reel media insights from graph endpoint', function (): void {
    $mediaTypeResponse = \Mockery::mock(ResponseInterface::class);
    $mediaTypeResponse->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'media_type' => 'REEL',
        ]);

    $insightsResponse = \Mockery::mock(ResponseInterface::class);
    $insightsResponse->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'data' => [
                ['name' => 'impressions', 'values' => [['value' => 120]]],
                ['name' => 'reach', 'values' => [['value' => 101]]],
                ['name' => 'saved', 'values' => [['value' => 20]]],
                ['name' => 'likes', 'values' => [['value' => 70]]],
                ['name' => 'comments', 'values' => [['value' => 15]]],
                ['name' => 'shares', 'values' => [['value' => 5]]],
                ['name' => 'plays', 'values' => [['value' => 400]]],
            ],
        ]);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000002'
                && $method === 'GET'
                && $params === [
                    'fields' => 'media_type',
                ];
        })
        ->andReturn($mediaTypeResponse);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000002/insights'
                && $method === 'GET'
                && $params === [
                    'metric' => 'impressions,reach,saved,likes,comments,shares,plays',
                ];
        })
        ->andReturn($insightsResponse);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->getMediaInsights('17900000000000002'))->toBe([
        'impressions' => 120,
        'reach' => 101,
        'saved' => 20,
        'likes' => 70,
        'comments' => 15,
        'shares' => 5,
        'plays' => 400,
    ]);
});

it('gets story media insights from graph endpoint', function (): void {
    $mediaTypeResponse = \Mockery::mock(ResponseInterface::class);
    $mediaTypeResponse->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'media_type' => 'STORY',
        ]);

    $insightsResponse = \Mockery::mock(ResponseInterface::class);
    $insightsResponse->shouldReceive('getContent')
        ->once()
        ->andReturn([
            'data' => [
                ['name' => 'impressions', 'values' => [['value' => 44]]],
                ['name' => 'reach', 'values' => [['value' => 39]]],
                ['name' => 'replies', 'values' => [['value' => 4]]],
                ['name' => 'exits', 'values' => [['value' => 2]]],
            ],
        ]);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000003'
                && $method === 'GET'
                && $params === [
                    'fields' => 'media_type',
                ];
        })
        ->andReturn($mediaTypeResponse);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/17900000000000003/insights'
                && $method === 'GET'
                && $params === [
                    'metric' => 'impressions,reach,replies,exits',
                ];
        })
        ->andReturn($insightsResponse);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

    expect($client->getMediaInsights('17900000000000003'))->toBe([
        'impressions' => 44,
        'reach' => 39,
        'replies' => 4,
        'exits' => 2,
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
                'account_type' => 'BUSINESS',
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
            'account_type',
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
        'account_type' => 'BUSINESS',
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
        'followers_count' => null,
        'following_count' => null,
        'media_count' => null,
        'account_type' => null,
    ]);
});
