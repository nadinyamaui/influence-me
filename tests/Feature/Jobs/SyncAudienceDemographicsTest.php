<?php

use App\Enums\DemographicType;
use App\Jobs\SyncAudienceDemographics;
use App\Models\AudienceDemographic;
use App\Models\InstagramAccount;
use App\Services\Facebook\Client as FacebookClient;

it('syncs audience demographics and replaces old records with a fresh snapshot', function (): void {
    $account = InstagramAccount::factory()->create([
        'followers_count' => 1500,
    ]);
    $otherAccount = InstagramAccount::factory()->create();

    AudienceDemographic::factory()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::City,
        'dimension' => 'Old City',
        'value' => 0.01,
    ]);
    AudienceDemographic::factory()->create([
        'instagram_account_id' => $otherAccount->id,
        'type' => DemographicType::City,
        'dimension' => 'Untouched City',
        'value' => 0.50,
    ]);

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getAudienceDemographics')
        ->once()
        ->andReturn([
            'audience_gender_age' => [
                'F.18-24' => 0.12,
                'M.18-24' => 0.08,
                'M.25-34' => 0.25,
            ],
            'audience_city' => [
                'London, England' => 0.08,
                'Paris, France' => 0.03,
            ],
            'audience_country' => [
                'US' => 0.35,
                'GB' => 0.12,
            ],
        ]);

    app()->bind(FacebookClient::class, function ($app, $parameters) use ($facebookClient, $account) {
        expect($parameters['access_token'] ?? null)->toBe($account->access_token);

        return $facebookClient;
    });

    app(SyncAudienceDemographics::class, ['account' => $account])->handle();

    $accountDemographics = AudienceDemographic::query()
        ->where('instagram_account_id', $account->id)
        ->get();

    expect($accountDemographics)->toHaveCount(8)
        ->and(
            $accountDemographics->contains(
                fn (AudienceDemographic $demographic): bool => $demographic->type === DemographicType::Age
                    && $demographic->dimension === '18-24'
                    && $demographic->value === '0.20'
            )
        )->toBeTrue()
        ->and(
            $accountDemographics->contains(
                fn (AudienceDemographic $demographic): bool => $demographic->type === DemographicType::Age
                    && $demographic->dimension === '25-34'
                    && $demographic->value === '0.25'
            )
        )->toBeTrue()
        ->and(
            $accountDemographics->contains(
                fn (AudienceDemographic $demographic): bool => $demographic->type === DemographicType::Gender
                    && $demographic->dimension === 'Female'
                    && $demographic->value === '0.12'
            )
        )->toBeTrue()
        ->and(
            $accountDemographics->contains(
                fn (AudienceDemographic $demographic): bool => $demographic->type === DemographicType::Gender
                    && $demographic->dimension === 'Male'
                    && $demographic->value === '0.33'
            )
        )->toBeTrue()
        ->and(
            $accountDemographics->contains(
                fn (AudienceDemographic $demographic): bool => $demographic->type === DemographicType::City
                    && $demographic->dimension === 'London, England'
                    && $demographic->value === '0.08'
            )
        )->toBeTrue()
        ->and(
            $accountDemographics->contains(
                fn (AudienceDemographic $demographic): bool => $demographic->type === DemographicType::Country
                    && $demographic->dimension === 'US'
                    && $demographic->value === '0.35'
            )
        )->toBeTrue();

    expect(
        AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->where('dimension', 'Old City')
            ->exists()
    )->toBeFalse();

    expect(
        AudienceDemographic::query()
            ->where('instagram_account_id', $otherAccount->id)
            ->where('dimension', 'Untouched City')
            ->exists()
    )->toBeTrue();
});

it('skips demographics sync for accounts with fewer than one hundred followers', function (): void {
    $account = InstagramAccount::factory()->create([
        'followers_count' => 99,
    ]);

    $existingDemographic = AudienceDemographic::factory()->create([
        'instagram_account_id' => $account->id,
    ]);

    $facebookClient = \Mockery::mock(FacebookClient::class);
    $facebookClient->shouldReceive('getAudienceDemographics')->never();
    app()->bind(FacebookClient::class, fn () => $facebookClient);

    app(SyncAudienceDemographics::class, ['account' => $account])->handle();

    expect(
        AudienceDemographic::query()
            ->where('instagram_account_id', $account->id)
            ->count()
    )->toBe(1)
        ->and($existingDemographic->fresh())->not->toBeNull();
});

it('configures queue settings for demographics sync workloads', function (): void {
    $account = InstagramAccount::factory()->create();
    $job = new SyncAudienceDemographics($account);

    expect($job->queue)->toBe('instagram-sync')
        ->and($job->tries)->toBe(3);
});
