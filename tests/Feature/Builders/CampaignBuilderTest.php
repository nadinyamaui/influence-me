<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\Proposal;
use App\Models\SocialAccount;
use App\Models\User;

it('scopes campaigns by user and client and orders by name', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $ownerOtherClient = Client::factory()->for($owner)->create();
    $outsiderClient = Client::factory()->for($outsider)->create();

    Campaign::factory()->for($ownerClient)->create(['name' => 'Zulu Launch']);
    Campaign::factory()->for($ownerClient)->create(['name' => 'Alpha Launch']);
    Campaign::factory()->for($ownerOtherClient)->create(['name' => 'Owner Other Campaign']);
    Campaign::factory()->for($outsiderClient)->create(['name' => 'Outsider Campaign']);

    $names = Campaign::query()
        ->forUser($owner->id)
        ->forClient($ownerClient->id)
        ->orderedByName()
        ->pluck('name')
        ->all();

    expect($names)->toBe(['Alpha Launch', 'Zulu Launch']);
});

it('loads campaign proposal relation and instagram media count', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->create();
    $campaign = Campaign::factory()->for($client)->create([
        'proposal_id' => $proposal->id,
    ]);

    $account = SocialAccount::factory()->for($user)->create();
    $firstMedia = InstagramMedia::factory()->for($account)->create();
    $secondMedia = InstagramMedia::factory()->for($account)->create();
    $campaign->instagramMedia()->attach([$firstMedia->id, $secondMedia->id]);

    $resolved = Campaign::query()
        ->forClient($client->id)
        ->withProposalAndMediaCount()
        ->firstOrFail();

    expect($resolved->relationLoaded('proposal'))->toBeTrue()
        ->and($resolved->proposal?->id)->toBe($proposal->id)
        ->and($resolved->instagram_media_count)->toBe(2);
});

it('loads campaign instagram media ordered by newest published first', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $campaign = Campaign::factory()->for($client)->create();
    $account = SocialAccount::factory()->for($user)->create();

    $olderMedia = InstagramMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(3),
    ]);
    $newerMedia = InstagramMedia::factory()->for($account)->create([
        'published_at' => now()->subDay(),
    ]);

    $campaign->instagramMedia()->attach([$olderMedia->id, $newerMedia->id]);

    $resolved = Campaign::query()
        ->whereKey($campaign->id)
        ->withInstagramMediaOrderedByPublishedAtDesc()
        ->firstOrFail();

    expect($resolved->relationLoaded('instagramMedia'))->toBeTrue()
        ->and($resolved->instagramMedia->pluck('id')->all())->toBe([$newerMedia->id, $olderMedia->id]);
});

it('loads campaign instagram media analytics columns only', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $campaign = Campaign::factory()->for($client)->create();
    $account = SocialAccount::factory()->for($user)->create();

    $media = InstagramMedia::factory()->for($account)->create([
        'caption' => 'Should not be selected',
        'reach' => 4500,
        'engagement_rate' => 4.25,
    ]);

    $campaign->instagramMedia()->attach([$media->id]);

    $resolved = Campaign::query()
        ->whereKey($campaign->id)
        ->withInstagramMediaAnalyticsMetrics()
        ->firstOrFail();

    $attributes = $resolved->instagramMedia->firstOrFail()->getAttributes();

    expect($resolved->relationLoaded('instagramMedia'))->toBeTrue()
        ->and($attributes)->toHaveKeys(['id', 'reach', 'engagement_rate'])
        ->and($attributes)->not->toHaveKey('caption');
});
