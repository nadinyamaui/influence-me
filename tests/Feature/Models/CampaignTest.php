<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\SocialAccountMedia;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('creates campaign records with nullable proposal support', function (): void {
    $client = Client::factory()->create();
    $proposal = Proposal::factory()->for($client->user)->for($client)->create();

    $campaign = Campaign::factory()->for($client)->for($proposal)->create([
        'name' => 'Spring Launch',
    ]);

    $campaignWithoutProposal = Campaign::factory()->for($client)->create([
        'proposal_id' => null,
    ]);

    expect($campaign->client)->toBeInstanceOf(Client::class)
        ->and($campaign->proposal)->toBeInstanceOf(Proposal::class)
        ->and($campaignWithoutProposal->proposal)->toBeNull();
});

it('defines client proposal and media relationships', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->create();
    $campaign = Campaign::factory()->for($client)->for($proposal)->create();
    $media = SocialAccountMedia::factory()->create();

    $campaign->instagramMedia()->attach($media->id, [
        'notes' => 'Launch placement',
    ]);

    expect($campaign->client())->toBeInstanceOf(BelongsTo::class)
        ->and($campaign->proposal())->toBeInstanceOf(BelongsTo::class)
        ->and($campaign->instagramMedia())->toBeInstanceOf(BelongsToMany::class)
        ->and($campaign->instagramMedia)->toHaveCount(1)
        ->and($campaign->instagramMedia->first()->id)->toBe($media->id);
});
