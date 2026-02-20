<?php

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\User;

it('searches clients across name email and company columns', function (): void {
    $user = User::factory()->create();

    $nameMatch = Client::factory()->for($user)->brand()->create([
        'name' => 'Acme Ventures',
        'email' => 'name-match@example.com',
        'company_name' => 'Name Match Inc',
    ]);

    $emailMatch = Client::factory()->for($user)->brand()->create([
        'name' => 'Other Name',
        'email' => 'billing@acme-mail.com',
        'company_name' => 'Email Match Inc',
    ]);

    $companyMatch = Client::factory()->for($user)->brand()->create([
        'name' => 'Another Name',
        'email' => 'company-match@example.com',
        'company_name' => 'Acme Commerce',
    ]);

    Client::factory()->for($user)->individual()->create([
        'name' => 'No Match',
        'email' => 'no-match@example.com',
        'company_name' => null,
    ]);

    $ids = Client::query()
        ->search('acme')
        ->pluck('id')
        ->all();

    expect($ids)->toEqualCanonicalizing([
        $nameMatch->id,
        $emailMatch->id,
        $companyMatch->id,
    ]);
});

it('filters clients by valid type and ignores invalid type values', function (): void {
    $user = User::factory()->create();

    $brandClient = Client::factory()->for($user)->brand()->create();
    $individualClient = Client::factory()->for($user)->individual()->create();

    $brandIds = Client::query()
        ->filterByType(ClientType::Brand->value)
        ->pluck('id')
        ->all();

    $allIds = Client::query()
        ->filterByType('unsupported-type')
        ->pluck('id')
        ->all();

    expect($brandIds)->toBe([$brandClient->id])
        ->and($allIds)->toEqualCanonicalizing([$brandClient->id, $individualClient->id]);
});

it('returns an unmodified query for blank search terms', function (): void {
    $user = User::factory()->create();

    $firstClient = Client::factory()->for($user)->create();
    $secondClient = Client::factory()->for($user)->create();

    $ids = Client::query()
        ->search('   ')
        ->pluck('id')
        ->all();

    expect($ids)->toEqualCanonicalizing([$firstClient->id, $secondClient->id]);
});
