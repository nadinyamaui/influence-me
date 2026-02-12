<?php

use App\Jobs\SyncAudienceDemographics;
use App\Models\AudienceDemographic;
use App\Models\InstagramAccount;
use App\Services\Facebook\Client as FacebookClient;

it('replaces existing demographics with a fresh snapshot', function (): void {
    $account = InstagramAccount::factory()->create([
        'followers_count' => 500,
    ]);

    AudienceDemographic::factory()->create([
        'instagram_account_id' => $account->id,
        'type' => 'age',
        'dimension' => '18-24',
        'value' => 42,
    ]);
    AudienceDemographic::factory()->create([
        'instagram_account_id' => $account->id,
        'type' => 'city',
        'dimension' => 'Paris',
        'value' => 15,
    ]);

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getAudienceDemographics')
        ->once()
        ->andReturn([
            'age' => [
                '25-34' => 12,
                '35-44' => 8,
            ],
            'gender' => [
                'Male' => 40,
                'Female' => 60,
            ],
        ]);

    app()->bind(FacebookClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncAudienceDemographics::class, ['account' => $account])->handle();

    expect(AudienceDemographic::query()
        ->where('instagram_account_id', $account->id)
        ->where('dimension', '18-24')
        ->exists())->toBeFalse()
        ->and(AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->where('dimension', 'Paris')
            ->exists())->toBeFalse()
        ->and(AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->count())->toBe(4)
        ->and(AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->where('type', 'age')
            ->where('dimension', '25-34')
            ->first()
            ?->value)->toBe('60.00')
        ->and(AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->where('type', 'age')
            ->where('dimension', '35-44')
            ->first()
            ?->value)->toBe('40.00')
        ->and(AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->where('type', 'gender')
            ->where('dimension', 'Male')
            ->first()
            ?->value)->toBe('40.00')
        ->and(AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->where('type', 'gender')
            ->where('dimension', 'Female')
            ->first()
            ?->value)->toBe('60.00');
});

it('skips demographics sync for accounts with fewer than 100 followers', function (): void {
    $account = InstagramAccount::factory()->create([
        'followers_count' => 99,
    ]);

    AudienceDemographic::factory()->create([
        'instagram_account_id' => $account->id,
        'type' => 'country',
        'dimension' => 'US',
        'value' => 10,
    ]);

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldNotReceive('getAudienceDemographics');
    app()->bind(FacebookClient::class, fn () => $facebookClient);

    app(SyncAudienceDemographics::class, ['account' => $account])->handle();

    expect(AudienceDemographic::query()
        ->where('instagram_account_id', $account->id)
        ->count())->toBe(1);
});

it('configures queue settings for demographics sync workloads', function (): void {
    $account = InstagramAccount::factory()->create();
    $job = new SyncAudienceDemographics($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});
