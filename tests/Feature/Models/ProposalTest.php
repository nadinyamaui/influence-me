<?php

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('creates valid proposal records with factory defaults and casts', function (): void {
    $proposal = Proposal::factory()->create();

    expect($proposal->user)->toBeInstanceOf(User::class)
        ->and($proposal->client)->toBeInstanceOf(Client::class)
        ->and($proposal->client->user_id)->toBe($proposal->user_id)
        ->and($proposal->title)->not->toBeEmpty()
        ->and($proposal->content)->toContain('#')
        ->and($proposal->status)->toBeInstanceOf(ProposalStatus::class)
        ->and($proposal->status)->toBe(ProposalStatus::Draft)
        ->and($proposal->sent_at)->toBeNull()
        ->and($proposal->responded_at)->toBeNull();
});

it('supports draft sent approved rejected and revised factory states', function (): void {
    $draft = Proposal::factory()->draft()->create();
    $sent = Proposal::factory()->sent()->create();
    $approved = Proposal::factory()->approved()->create();
    $rejected = Proposal::factory()->rejected()->create();
    $revised = Proposal::factory()->revised()->create();

    expect($draft->status)->toBe(ProposalStatus::Draft)
        ->and($draft->sent_at)->toBeNull()
        ->and($draft->responded_at)->toBeNull()
        ->and($sent->status)->toBe(ProposalStatus::Sent)
        ->and($sent->sent_at)->not->toBeNull()
        ->and($sent->responded_at)->toBeNull()
        ->and($approved->status)->toBe(ProposalStatus::Approved)
        ->and($approved->responded_at)->not->toBeNull()
        ->and($rejected->status)->toBe(ProposalStatus::Rejected)
        ->and($rejected->responded_at)->not->toBeNull()
        ->and($revised->status)->toBe(ProposalStatus::Revised)
        ->and($revised->revision_notes)->not->toBeNull()
        ->and($revised->responded_at)->not->toBeNull();
});

it('defines user and client relationships with typed return values', function (): void {
    $proposal = Proposal::factory()->create();

    $userReturnType = (new ReflectionMethod(Proposal::class, 'user'))
        ->getReturnType()?->getName();
    $clientReturnType = (new ReflectionMethod(Proposal::class, 'client'))
        ->getReturnType()?->getName();

    expect($proposal->user())->toBeInstanceOf(BelongsTo::class)
        ->and($proposal->client())->toBeInstanceOf(BelongsTo::class)
        ->and($proposal->user)->toBeInstanceOf(User::class)
        ->and($proposal->client)->toBeInstanceOf(Client::class)
        ->and($userReturnType)->toBe(BelongsTo::class)
        ->and($clientReturnType)->toBe(BelongsTo::class);
});

it('defines user proposals relationship', function (): void {
    $user = User::factory()->create();

    Proposal::factory()->for($user)->create();
    Proposal::factory()->for($user)->create();

    $proposalsReturnType = (new ReflectionMethod(User::class, 'proposals'))
        ->getReturnType()?->getName();

    expect($user->proposals())->toBeInstanceOf(HasMany::class)
        ->and($proposalsReturnType)->toBe(HasMany::class)
        ->and($user->proposals)->toHaveCount(2);
});
