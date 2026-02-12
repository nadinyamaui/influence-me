<?php

use App\Exceptions\InstagramApiException;
use App\Exceptions\InstagramTokenExpiredException;
use App\Services\Facebook\Client;
use FacebookAds\Api;
use FacebookAds\Http\ResponseInterface;
use Illuminate\Support\Facades\Http;

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
    $accountsResponse = [
        'data' => [
            [
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
            ],
            [
                'id' => 'page-2',
                'name' => 'No Instagram Page',
                'access_token' => 'page-token-2',
            ],
        ],
    ];

    $response = \Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->once()
        ->andReturn($accountsResponse);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/me/accounts'
                && $method === 'GET'
                && is_string($params['fields'] ?? null)
                && str_contains($params['fields'], 'instagram_business_account{');
        })
        ->andReturn($response);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

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
    $accountsResponse = [
        'data' => [
            [
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
            ],
            [
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
            ],
            [
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
            ],
        ],
    ];

    $response = \Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('getContent')
        ->once()
        ->andReturn($accountsResponse);

    $api = \Mockery::mock(Api::class);
    $api->shouldReceive('call')
        ->once()
        ->withArgs(function ($path, $method, $params): bool {
            return $path === '/me/accounts'
                && $method === 'GET'
                && is_string($params['fields'] ?? null)
                && str_contains($params['fields'], 'instagram_business_account{');
        })
        ->andReturn($response);

    $clientReflection = new ReflectionClass(Client::class);
    $client = $clientReflection->newInstanceWithoutConstructor();

    $apiProperty = $clientReflection->getProperty('api');
    $apiProperty->setAccessible(true);
    $apiProperty->setValue($client, $api);

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

it('gets instagram media from graph endpoint', function (): void {
    Http::fake([
        'https://graph.instagram.com/v21.0/me/media*' => Http::response([
            'data' => [
                [
                    'id' => 'media-1',
                    'media_type' => 'IMAGE',
                ],
            ],
        ], 200),
    ]);

    $client = new Client('instagram-access-token');
    $response = $client->getMedia('cursor-123');

    expect($response['data'][0]['id'])->toBe('media-1');

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://graph.instagram.com/v21.0/me/media?fields=id%2Ccaption%2Cmedia_type%2Cmedia_product_type%2Cmedia_url%2Cthumbnail_url%2Cpermalink%2Ctimestamp%2Clike_count%2Ccomments_count&access_token=instagram-access-token&after=cursor-123';
    });
});

it('throws typed exception when instagram token is expired', function (): void {
    Http::fake([
        'https://graph.instagram.com/v21.0/me/media*' => Http::response([
            'error' => [
                'code' => 190,
            ],
        ], 400),
    ]);

    $client = new Client('expired-token');
    $client->getMedia();
})->throws(InstagramTokenExpiredException::class);

it('throws generic instagram api exception for non-token api errors', function (): void {
    Http::fake([
        'https://graph.instagram.com/v21.0/me/media*' => Http::response([
            'error' => [
                'code' => 4,
            ],
        ], 400),
    ]);

    $client = new Client('invalid-token');
    $client->getMedia();
})->throws(InstagramApiException::class);
