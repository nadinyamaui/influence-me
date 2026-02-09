<?php

use App\Models\Client;
use App\Models\InstagramMedia;

it('stores pivot data with timestamps when linking media to a client', function (): void {
    $client = Client::factory()->create();
    $media = InstagramMedia::factory()->create();

    $client->instagramMedia()->attach($media->id, [
        'campaign_name' => 'Spring Launch',
        'notes' => 'Main feed feature',
    ]);

    $linkedMedia = $client->instagramMedia()->first();

    expect($linkedMedia)->not->toBeNull()
        ->and($linkedMedia->pivot->campaign_name)->toBe('Spring Launch')
        ->and($linkedMedia->pivot->notes)->toBe('Main feed feature')
        ->and($linkedMedia->pivot->created_at)->not->toBeNull()
        ->and($linkedMedia->pivot->updated_at)->not->toBeNull();
});

it('supports detaching linked media from a client relationship', function (): void {
    $client = Client::factory()->create();
    $media = InstagramMedia::factory()->create();

    $client->instagramMedia()->attach($media->id, [
        'campaign_name' => 'Holiday Push',
        'notes' => 'Temporary campaign',
    ]);

    expect($client->instagramMedia)->toHaveCount(1)
        ->and($media->clients)->toHaveCount(1);

    $client->instagramMedia()->detach($media->id);

    expect($client->fresh()->instagramMedia)->toHaveCount(0)
        ->and($media->fresh()->clients)->toHaveCount(0);
});

it('supports detaching linked clients from a media relationship', function (): void {
    $client = Client::factory()->create();
    $media = InstagramMedia::factory()->create();

    $media->clients()->attach($client->id, [
        'campaign_name' => 'Product Teaser',
        'notes' => 'Story sequence',
    ]);

    expect($media->clients)->toHaveCount(1)
        ->and($client->instagramMedia)->toHaveCount(1);

    $media->clients()->detach($client->id);

    expect($media->fresh()->clients)->toHaveCount(0)
        ->and($client->fresh()->instagramMedia)->toHaveCount(0);
});
