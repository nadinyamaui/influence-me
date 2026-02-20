<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\SocialAccountMedia;

it('stores pivot data with timestamps when linking media to a campaign', function (): void {
    $client = Client::factory()->create();
    $campaign = Campaign::factory()->for($client)->create();
    $media = SocialAccountMedia::factory()->create();

    $campaign->instagramMedia()->attach($media->id, [
        'notes' => 'Main feed feature',
    ]);

    $linkedMedia = $campaign->instagramMedia()->first();

    expect($linkedMedia)->not->toBeNull()
        ->and($linkedMedia->pivot->notes)->toBe('Main feed feature')
        ->and($linkedMedia->pivot->created_at)->not->toBeNull()
        ->and($linkedMedia->pivot->updated_at)->not->toBeNull();
});

it('supports detaching linked media from a campaign relationship', function (): void {
    $client = Client::factory()->create();
    $campaign = Campaign::factory()->for($client)->create();
    $media = SocialAccountMedia::factory()->create();

    $campaign->instagramMedia()->attach($media->id, [
        'notes' => 'Temporary campaign',
    ]);

    expect($campaign->instagramMedia)->toHaveCount(1)
        ->and($media->campaigns)->toHaveCount(1);

    $campaign->instagramMedia()->detach($media->id);

    expect($campaign->fresh()->instagramMedia)->toHaveCount(0)
        ->and($media->fresh()->campaigns)->toHaveCount(0);
});

it('supports detaching linked campaigns from a media relationship', function (): void {
    $client = Client::factory()->create();
    $campaign = Campaign::factory()->for($client)->create();
    $media = SocialAccountMedia::factory()->create();

    $media->campaigns()->attach($campaign->id, [
        'notes' => 'Story sequence',
    ]);

    expect($media->campaigns)->toHaveCount(1)
        ->and($campaign->instagramMedia)->toHaveCount(1);

    $media->campaigns()->detach($campaign->id);

    expect($media->fresh()->campaigns)->toHaveCount(0)
        ->and($campaign->fresh()->instagramMedia)->toHaveCount(0);
});
