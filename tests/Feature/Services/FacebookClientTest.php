<?php

use App\Services\Facebook\Client;
use FacebookAds\Api;
use FacebookAds\Http\ResponseInterface;

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
            'id' => 'ig-1',
            'name' => 'Creator Page',
            'username' => 'creator.one',
            'biography' => 'Creator bio',
            'profile_picture_url' => 'https://example.com/pic.jpg',
            'followers_count' => 1200,
            'follows_count' => 450,
            'media_count' => 88,
            'access_token' => 'page-token-1',
        ],
    ]);
});
