<?php

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('scopes proposals to authenticated user when no user id is provided', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $outsiderClient = Client::factory()->for($outsider)->create();

    $ownerProposal = Proposal::factory()->for($owner)->for($ownerClient)->create();
    Proposal::factory()->for($outsider)->for($outsiderClient)->create();

    actingAs($owner);

    $ids = Proposal::query()
        ->forUser()
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$ownerProposal->id]);
});

it('scopes proposals by client and status', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $otherClient = Client::factory()->for($user)->create();

    $matchingProposal = Proposal::factory()->for($user)->for($client)->create([
        'status' => ProposalStatus::Sent,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'status' => ProposalStatus::Draft,
    ]);

    Proposal::factory()->for($user)->for($otherClient)->create([
        'status' => ProposalStatus::Sent,
    ]);

    $ids = Proposal::query()
        ->forUser($user->id)
        ->forClient($client->id)
        ->filterByStatus(ProposalStatus::Sent->value)
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$matchingProposal->id]);
});

it('ignores invalid proposal status filters and orders latest first', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $olderProposal = Proposal::factory()->for($user)->for($client)->create([
        'created_at' => '2026-02-01 10:00:00',
    ]);

    $newerProposal = Proposal::factory()->for($user)->for($client)->create([
        'created_at' => '2026-02-10 10:00:00',
    ]);

    $ids = Proposal::query()
        ->forUser($user->id)
        ->filterByStatus('unsupported-status')
        ->latestFirst()
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$newerProposal->id, $olderProposal->id]);
});
