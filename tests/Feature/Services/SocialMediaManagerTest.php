<?php

use App\Enums\SocialNetwork;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\InstagramGraphService;
use App\Services\SocialMedia\SocialMediaInterface;
use App\Services\SocialMedia\SocialMediaManager;

it('returns the instagram social media service for instagram accounts', function (): void {
    $account = SocialAccount::factory()->create([
        'social_network' => SocialNetwork::Instagram,
    ]);

    $service = \Mockery::mock(InstagramGraphService::class);
    app()->bind(InstagramGraphService::class, fn () => $service);

    $resolved = app(SocialMediaManager::class)->forAccount($account);

    expect($resolved)->toBe($service)
        ->and($resolved)->toBeInstanceOf(SocialMediaInterface::class);
});

it('throws for social networks without a configured service', function (): void {
    $account = SocialAccount::factory()->create([
        'social_network' => SocialNetwork::Tiktok,
    ]);

    expect(fn () => app(SocialMediaManager::class)->forAccount($account))
        ->toThrow(\InvalidArgumentException::class, 'No social media service is configured for network [tiktok].');
});
