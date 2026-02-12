<?php

use App\Enums\DemographicType;
use App\Models\AudienceDemographic;
use App\Models\InstagramAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('creates valid audience demographic records with factory defaults and casts', function (): void {
    $account = InstagramAccount::factory()->create();
    $demographic = AudienceDemographic::query()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::Age,
        'dimension' => '18-24',
        'value' => 42.5,
        'recorded_at' => now(),
    ]);

    expect($demographic->instagramAccount)->toBeInstanceOf(InstagramAccount::class)
        ->and($demographic->type)->toBeInstanceOf(DemographicType::class)
        ->and($demographic->dimension)->not->toBeEmpty()
        ->and($demographic->value)->toBeString();
});

it('supports age gender city and country demographic types', function (): void {
    $account = InstagramAccount::factory()->create();
    $age = AudienceDemographic::query()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::Age,
        'dimension' => '25-34',
        'value' => 15.3,
        'recorded_at' => now(),
    ]);
    $gender = AudienceDemographic::query()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::Gender,
        'dimension' => 'Female',
        'value' => 51.1,
        'recorded_at' => now(),
    ]);
    $city = AudienceDemographic::query()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::City,
        'dimension' => 'Paris',
        'value' => 5.4,
        'recorded_at' => now(),
    ]);
    $country = AudienceDemographic::query()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::Country,
        'dimension' => 'FR',
        'value' => 12.7,
        'recorded_at' => now(),
    ]);

    expect($age->type)->toBe(DemographicType::Age)
        ->and(in_array($age->dimension, ['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'], true))->toBeTrue()
        ->and($gender->type)->toBe(DemographicType::Gender)
        ->and(in_array($gender->dimension, ['Male', 'Female'], true))->toBeTrue()
        ->and($city->type)->toBe(DemographicType::City)
        ->and($city->dimension)->not->toBeEmpty()
        ->and($country->type)->toBe(DemographicType::Country)
        ->and($country->dimension)->not->toBeEmpty();
});

it('defines instagram account relationship', function (): void {
    $account = InstagramAccount::factory()->create();
    $demographic = AudienceDemographic::query()->create([
        'instagram_account_id' => $account->id,
        'type' => DemographicType::City,
        'dimension' => 'Berlin',
        'value' => 8.2,
        'recorded_at' => now(),
    ]);

    expect($demographic->instagramAccount())->toBeInstanceOf(BelongsTo::class)
        ->and($demographic->instagramAccount)->toBeInstanceOf(InstagramAccount::class);
});
