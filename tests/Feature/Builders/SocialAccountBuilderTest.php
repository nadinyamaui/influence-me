<?php

use App\Models\SocialAccount;
use App\Models\User;

it('scopes instagram accounts to a user and filters by a specific account id', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerFirstAccount = SocialAccount::factory()->for($owner)->create();
    $ownerSecondAccount = SocialAccount::factory()->for($owner)->create();
    SocialAccount::factory()->for($outsider)->create();

    $ids = SocialAccount::query()
        ->forUser($owner->id)
        ->filterByAccount((string) $ownerSecondAccount->id)
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$ownerSecondAccount->id])
        ->and($ids)->not->toContain($ownerFirstAccount->id);
});

it('keeps all user instagram accounts when the all filter is selected', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerFirstAccount = SocialAccount::factory()->for($owner)->create();
    $ownerSecondAccount = SocialAccount::factory()->for($owner)->create();
    SocialAccount::factory()->for($outsider)->create();

    $ids = SocialAccount::query()
        ->forUser($owner->id)
        ->filterByAccount('all')
        ->pluck('id')
        ->all();

    expect($ids)->toEqualCanonicalizing([
        $ownerFirstAccount->id,
        $ownerSecondAccount->id,
    ]);
});
