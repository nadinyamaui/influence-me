<?php

use App\Enums\DemographicType;
use App\Models\AudienceDemographic;
use App\Models\SocialAccount;
use App\Models\User;

it('scopes audience demographics by user account and demographic type and orders by value', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerAccount = SocialAccount::factory()->for($owner)->create();
    $secondaryOwnerAccount = SocialAccount::factory()->for($owner)->create();
    $outsiderAccount = SocialAccount::factory()->for($outsider)->create();

    AudienceDemographic::factory()->for($ownerAccount)->create([
        'type' => DemographicType::Age,
        'dimension' => '35-44',
        'value' => 40,
    ]);
    AudienceDemographic::factory()->for($ownerAccount)->create([
        'type' => DemographicType::Age,
        'dimension' => '25-34',
        'value' => 30,
    ]);
    AudienceDemographic::factory()->for($ownerAccount)->create([
        'type' => DemographicType::Age,
        'dimension' => '18-24',
        'value' => 30,
    ]);
    AudienceDemographic::factory()->for($ownerAccount)->create([
        'type' => DemographicType::Gender,
        'dimension' => 'Female',
        'value' => 90,
    ]);

    AudienceDemographic::factory()->for($secondaryOwnerAccount)->create([
        'type' => DemographicType::Age,
        'dimension' => '45-54',
        'value' => 99,
    ]);

    AudienceDemographic::factory()->for($outsiderAccount)->create([
        'type' => DemographicType::Age,
        'dimension' => '55-64',
        'value' => 99,
    ]);

    $dimensions = AudienceDemographic::query()
        ->forUser($owner->id)
        ->filterByAccount((string) $ownerAccount->id)
        ->ofType(DemographicType::Age)
        ->orderedByValueDesc()
        ->pluck('dimension')
        ->all();

    expect($dimensions)->toBe(['35-44', '18-24', '25-34']);
});

it('does not constrain to a specific account when all account filter is used', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerFirstAccount = SocialAccount::factory()->for($owner)->create();
    $ownerSecondAccount = SocialAccount::factory()->for($owner)->create();
    $outsiderAccount = SocialAccount::factory()->for($outsider)->create();

    AudienceDemographic::factory()->for($ownerFirstAccount)->create();
    AudienceDemographic::factory()->for($ownerSecondAccount)->create();
    AudienceDemographic::factory()->for($outsiderAccount)->create();

    $count = AudienceDemographic::query()
        ->forUser($owner->id)
        ->filterByAccount('all')
        ->count();

    expect($count)->toBe(2);
});
