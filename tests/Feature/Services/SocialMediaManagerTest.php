<?php

use App\Enums\SocialNetwork;
use App\Models\SocialAccount;
use App\Services\SocialMedia\Instagram\Service;
use App\Services\SocialMedia\SocialMediaContract;
use App\Services\SocialMedia\Manager;

it('returns the instagram social media service for instagram accounts', function (): void {
    $account = SocialAccount::factory()->create([
        'social_network' => SocialNetwork::Instagram,
    ]);

    $service = \Mockery::mock(Service::class);
    app()->bind(Service::class, fn () => $service);

    $resolved = app(Manager::class)->forAccount($account);

    expect($resolved)->toBe($service)
        ->and($resolved)->toBeInstanceOf(SocialMediaContract::class);
});

it('throws for social networks without a configured service', function (): void {
    $account = SocialAccount::factory()->create([
        'social_network' => SocialNetwork::Tiktok,
    ]);

    expect(fn () => app(Manager::class)->forAccount($account))
        ->toThrow(\InvalidArgumentException::class, 'No social media service is configured for network [tiktok].');
});
