<?php

use App\Models\InstagramAccount;
use App\Models\User;

it('scopes instagram accounts to a user and filters by a specific account id', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerFirstAccount = InstagramAccount::factory()->for($owner)->create();
    $ownerSecondAccount = InstagramAccount::factory()->for($owner)->create();
    InstagramAccount::factory()->for($outsider)->create();

    $ids = InstagramAccount::query()
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

    $ownerFirstAccount = InstagramAccount::factory()->for($owner)->create();
    $ownerSecondAccount = InstagramAccount::factory()->for($owner)->create();
    InstagramAccount::factory()->for($outsider)->create();

    $ids = InstagramAccount::query()
        ->forUser($owner->id)
        ->filterByAccount('all')
        ->pluck('id')
        ->all();

    expect($ids)->toEqualCanonicalizing([
        $ownerFirstAccount->id,
        $ownerSecondAccount->id,
    ]);
});
