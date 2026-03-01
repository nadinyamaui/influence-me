<?php

use App\Exceptions\TikTokApiException;
use App\Exceptions\TikTokTokenExpiredException;
use App\Services\SocialMedia\Tiktok\Connector;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends requests to the tiktok api and includes bearer token', function (): void {
    Http::fake([
        'https://open.tiktokapis.com/v2/user/info/*' => Http::response([
            'data' => [
                'open_id' => 'open-123',
            ],
        ]),
    ]);

    $connector = new Connector('token-abc', 42);

    $response = $connector->get('/v2/user/info/', ['fields' => 'open_id']);

    expect($response)->toBe([
        'open_id' => 'open-123',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://open.tiktokapis.com/v2/user/info/?fields=open_id'
            && $request->method() === 'GET'
            && $request->hasHeader('Authorization', 'Bearer token-abc')
            && $request->hasHeader('Accept', 'application/json');
    });
});

it('returns full payload when response does not include data key', function (): void {
    Http::fake([
        'https://open.tiktokapis.com/v2/video/query/*' => Http::response([
            'videos' => [
                ['id' => '1'],
            ],
            'cursor' => 0,
        ]),
    ]);

    $connector = new Connector('token-abc');

    $response = $connector->post('/v2/video/query/', ['max_count' => 1]);

    expect($response)->toBe([
        'videos' => [
            ['id' => '1'],
        ],
        'cursor' => 0,
    ]);
});

it('maps unauthorized errors to token expired exception', function (): void {
    Http::fake([
        'https://open.tiktokapis.com/v2/user/info/*' => Http::response([
            'error' => [
                'code' => 'access_token_expired',
                'message' => 'Access token expired.',
            ],
        ], 401),
    ]);

    $connector = new Connector('token-abc', 99);

    expect(fn () => $connector->get('/v2/user/info/'))
        ->toThrow(function (TikTokTokenExpiredException $exception): void {
            expect($exception->getCode())->toBe(401)
                ->and($exception->accountId)->toBe(99)
                ->and($exception->endpoint)->toBe('/v2/user/info/')
                ->and($exception->apiErrorCode)->toBe('access_token_expired');
        });
});

it('flags rate limited failures on typed api exception', function (): void {
    Http::fake([
        'https://open.tiktokapis.com/v2/video/query/*' => Http::response([
            'error' => [
                'code' => 42900,
                'message' => 'Too many requests.',
            ],
        ], 429),
    ]);

    $connector = new Connector('token-abc', 7);

    expect(fn () => $connector->post('/v2/video/query/', ['max_count' => 5]))
        ->toThrow(function (TikTokApiException $exception): void {
            expect($exception)->not->toBeInstanceOf(TikTokTokenExpiredException::class)
                ->and($exception->rateLimited)->toBeTrue()
                ->and($exception->getCode())->toBe(429)
                ->and($exception->accountId)->toBe(7)
                ->and($exception->endpoint)->toBe('/v2/video/query/');
        });
});

it('does not map all forbidden responses to token expired exceptions', function (): void {
    Http::fake([
        'https://open.tiktokapis.com/v2/user/info/*' => Http::response([
            'error' => [
                'code' => 'scope_permission_missing',
                'message' => 'Permission denied for this endpoint.',
            ],
        ], 403),
    ]);

    $connector = new Connector('token-abc', 44);

    expect(fn () => $connector->get('/v2/user/info/'))
        ->toThrow(function (TikTokApiException $exception): void {
            expect($exception)->not->toBeInstanceOf(TikTokTokenExpiredException::class)
                ->and($exception->getCode())->toBe(403)
                ->and($exception->apiErrorCode)->toBe('scope_permission_missing');
        });
});

it('treats payloads with error code zero as successful responses', function (): void {
    Http::fake([
        'https://open.tiktokapis.com/v2/user/info/*' => Http::response([
            'error' => [
                'code' => 0,
                'message' => 'ok',
            ],
            'data' => [
                'open_id' => 'open-xyz',
            ],
        ], 200),
    ]);

    $connector = new Connector('token-abc');

    expect($connector->get('/v2/user/info/'))->toBe([
        'open_id' => 'open-xyz',
    ]);
});
