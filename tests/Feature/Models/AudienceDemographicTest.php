<?php

use App\Enums\DemographicType;
use App\Models\AudienceDemographic;
use App\Models\InstagramAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('creates valid audience demographic records with factory defaults and casts', function (): void {
    $demographic = AudienceDemographic::factory()->create();

    expect($demographic->instagramAccount)->toBeInstanceOf(InstagramAccount::class)
        ->and($demographic->type)->toBeInstanceOf(DemographicType::class)
        ->and($demographic->dimension)->not->toBeEmpty()
        ->and($demographic->value)->toBeString()
        ->and($demographic->recorded_at)->not->toBeNull();
});

it('supports age gender city and country factory states', function (): void {
    $age = AudienceDemographic::factory()->age()->create();
    $gender = AudienceDemographic::factory()->gender()->create();
    $city = AudienceDemographic::factory()->city()->create();
    $country = AudienceDemographic::factory()->country()->create();

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
    $demographic = AudienceDemographic::factory()->create();

    expect($demographic->instagramAccount())->toBeInstanceOf(BelongsTo::class)
        ->and($demographic->instagramAccount)->toBeInstanceOf(InstagramAccount::class);
});
