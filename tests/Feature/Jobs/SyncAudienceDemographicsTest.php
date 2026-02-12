<?php

use App\Jobs\SyncAudienceDemographics;
use App\Models\InstagramAccount;
use App\Services\Facebook\InstagramGraphService;

it('delegates audience demographics sync to instagram graph service', function (): void {
    $account = InstagramAccount::factory()->create();

    $instagramGraphService = \Mockery::mock(InstagramGraphService::class);
    $instagramGraphService->shouldReceive('syncAudienceDemographics')
        ->once();

    app()->bind(InstagramGraphService::class, fn () => $instagramGraphService);

    app(SyncAudienceDemographics::class, ['account' => $account])->handle();
});

it('configures queue settings for demographics sync workloads', function (): void {
    $account = InstagramAccount::factory()->create();
    $job = new SyncAudienceDemographics($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});
