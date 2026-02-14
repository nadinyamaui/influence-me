<?php

use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use App\Services\Content\ContentClientLinkService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

test('content client link service links a media item to a client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $service = app(ContentClientLinkService::class);

    $service->link($user, $media, $client, 'Service Campaign', 'Service Notes');

    $this->assertDatabaseHas('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $media->id,
        'campaign_name' => 'Service Campaign',
        'notes' => 'Service Notes',
    ]);
});

test('content client link service batch links media items', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $firstMedia = InstagramMedia::factory()->for($account)->create();
    $secondMedia = InstagramMedia::factory()->for($account)->create();

    $service = app(ContentClientLinkService::class);

    $service->batchLink(
        $user,
        InstagramMedia::query()->whereKey([$firstMedia->id, $secondMedia->id])->get(),
        $client,
        'Batch Service Campaign',
        null,
    );

    $this->assertDatabaseHas('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $firstMedia->id,
        'campaign_name' => 'Batch Service Campaign',
    ]);

    $this->assertDatabaseHas('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $secondMedia->id,
        'campaign_name' => 'Batch Service Campaign',
    ]);
});

test('content client link service unlinks media from a client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $media->clients()->attach($client->id);

    $service = app(ContentClientLinkService::class);

    $service->unlink($user, $media, $client);

    expect(DB::table('campaign_media')
        ->where('client_id', $client->id)
        ->where('instagram_media_id', $media->id)
        ->exists())->toBeFalse();
});

test('content client link service enforces ownership boundaries', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $ownerAccount = InstagramAccount::factory()->for($owner)->create();
    $ownerMedia = InstagramMedia::factory()->for($ownerAccount)->create();

    $service = app(ContentClientLinkService::class);

    expect(fn (): mixed => $service->link($outsider, $ownerMedia, $ownerClient, null, null))
        ->toThrow(AuthorizationException::class);
});
