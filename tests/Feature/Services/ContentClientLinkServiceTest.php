<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Content\ContentClientLinkService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('content client link service links a media item to a client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = SocialAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();
    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Service Campaign',
    ]);

    $service = app(ContentClientLinkService::class);

    $service->link($user, $media, $campaign, 'Service Notes');

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $media->id,
        'notes' => 'Service Notes',
    ]);
});

test('content client link service batch links media items', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = SocialAccount::factory()->for($user)->create();

    $firstMedia = InstagramMedia::factory()->for($account)->create();
    $secondMedia = InstagramMedia::factory()->for($account)->create();
    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Batch Service Campaign',
    ]);

    $service = app(ContentClientLinkService::class);

    $service->batchLink(
        $user,
        InstagramMedia::query()->whereKey([$firstMedia->id, $secondMedia->id])->get(),
        $campaign,
        null,
    );

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $firstMedia->id,
    ]);

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $secondMedia->id,
    ]);
});

test('content client link service unlinks media from a client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = SocialAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $campaign = Campaign::factory()->for($client)->create();
    $campaign->instagramMedia()->attach($media->id);

    $service = app(ContentClientLinkService::class);

    $service->unlink($user, $media, $client);

    expect(DB::table('campaign_media')
        ->where('campaign_id', $campaign->id)
        ->where('instagram_media_id', $media->id)
        ->exists())->toBeFalse();
});

test('content client link service enforces ownership boundaries', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $ownerCampaign = Campaign::factory()->for($ownerClient)->create();
    $ownerAccount = SocialAccount::factory()->for($owner)->create();
    $ownerMedia = InstagramMedia::factory()->for($ownerAccount)->create();

    $service = app(ContentClientLinkService::class);

    expect(fn (): mixed => $service->link($outsider, $ownerMedia, $ownerCampaign, null))
        ->toThrow(AuthorizationException::class);
});

test('content client link service prevents duplicate campaign media links', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = SocialAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();
    $campaign = Campaign::factory()->for($client)->create();

    $service = app(ContentClientLinkService::class);

    $service->link($user, $media, $campaign, 'Initial notes');

    expect(fn (): mixed => $service->link($user, $media, $campaign, 'Second notes'))
        ->toThrow(ValidationException::class);

    expect(DB::table('campaign_media')
        ->where('campaign_id', $campaign->id)
        ->where('instagram_media_id', $media->id)
        ->count())->toBe(1);
});
