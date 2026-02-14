<?php

use App\Enums\ClientType;
use App\Enums\MediaType;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('creates valid instagram media records with factory defaults and casts', function (): void {
    $media = InstagramMedia::factory()->create();

    expect($media->instagramAccount)->not->toBeNull()
        ->and($media->instagram_media_id)->not->toBeEmpty()
        ->and($media->media_type)->toBeInstanceOf(MediaType::class)
        ->and($media->published_at)->not->toBeNull()
        ->and($media->engagement_rate)->toBeString();
});

it('supports post reel story and high engagement factory states', function (): void {
    $post = InstagramMedia::factory()->post()->create();
    $reel = InstagramMedia::factory()->reel()->create();
    $story = InstagramMedia::factory()->story()->create();
    $highEngagement = InstagramMedia::factory()->highEngagement()->create();

    expect($post->media_type)->toBe(MediaType::Post)
        ->and($reel->media_type)->toBe(MediaType::Reel)
        ->and($story->media_type)->toBe(MediaType::Story)
        ->and($highEngagement->like_count)->toBeGreaterThanOrEqual(5000)
        ->and($highEngagement->comments_count)->toBeGreaterThanOrEqual(500)
        ->and((float) $highEngagement->engagement_rate)->toBeGreaterThanOrEqual(18);
});

it('defines instagram account and campaigns relationships', function (): void {
    $media = InstagramMedia::factory()->create();

    $client = Client::query()->create([
        'user_id' => User::factory()->create()->id,
        'name' => 'Acme Brand',
        'email' => 'team@acme.test',
        'company_name' => 'Acme',
        'type' => ClientType::Brand,
    ]);

    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Spring Launch',
    ]);

    $media->campaigns()->attach($campaign->id, [
        'notes' => 'Primary placement',
    ]);

    expect($media->instagramAccount())->toBeInstanceOf(BelongsTo::class)
        ->and($media->campaigns())->toBeInstanceOf(BelongsToMany::class)
        ->and($media->campaigns)->toHaveCount(1)
        ->and($media->campaigns->first()->id)->toBe($campaign->id)
        ->and($media->campaigns->first()->pivot->notes)->toBe('Primary placement');
});
