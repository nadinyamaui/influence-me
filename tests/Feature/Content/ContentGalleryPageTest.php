<?php

use App\Enums\MediaType;
use App\Livewire\Content\Index;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('guests are redirected to login from content gallery page', function (): void {
    $this->get(route('content.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view scoped content gallery page', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $account = InstagramAccount::factory()->for($user)->create([
        'username' => 'owneraccount',
    ]);

    $otherAccount = InstagramAccount::factory()->for($otherUser)->create();

    InstagramMedia::factory()->for($account)->create([
        'caption' => 'Owner gallery post',
        'engagement_rate' => 6.25,
    ]);

    InstagramMedia::factory()->for($otherAccount)->create([
        'caption' => 'Hidden outsider post',
    ]);

    $this->actingAs($user)
        ->get(route('content.index'))
        ->assertSuccessful()
        ->assertSee('Content')
        ->assertSee('Owner gallery post')
        ->assertSee('6.25%')
        ->assertDontSee('Hidden outsider post')
        ->assertSee('href="'.route('content.index').'"', false);
});

test('content gallery filters and sorting options work in query layer', function (): void {
    $user = User::factory()->create();

    $primaryAccount = InstagramAccount::factory()->for($user)->create();
    $secondaryAccount = InstagramAccount::factory()->for($user)->create();

    InstagramMedia::factory()->for($primaryAccount)->create([
        'caption' => 'Primary Post Item',
        'media_type' => MediaType::Post,
        'published_at' => now()->subDay(),
        'like_count' => 100,
        'reach' => 300,
        'engagement_rate' => 3.20,
    ]);

    InstagramMedia::factory()->for($primaryAccount)->create([
        'caption' => 'Primary Reel Item',
        'media_type' => MediaType::Reel,
        'published_at' => now()->subDays(2),
        'like_count' => 500,
        'reach' => 120,
        'engagement_rate' => 8.10,
    ]);

    InstagramMedia::factory()->for($secondaryAccount)->create([
        'caption' => 'Secondary Story Item',
        'media_type' => MediaType::Story,
        'published_at' => now()->subDays(3),
        'like_count' => 250,
        'reach' => 900,
        'engagement_rate' => 5.55,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('mediaType', MediaType::Reel->value)
        ->assertSee('Primary Reel Item')
        ->assertDontSee('Primary Post Item')
        ->assertDontSee('Secondary Story Item')
        ->set('mediaType', 'all')
        ->set('accountId', (string) $secondaryAccount->id)
        ->assertSee('Secondary Story Item')
        ->assertDontSee('Primary Post Item')
        ->set('accountId', 'all')
        ->set('dateRange', ['start' => now()->subDays(2)->format('Y-m-d'), 'end' => null])
        ->assertSee('Primary Post Item')
        ->assertSee('Primary Reel Item')
        ->assertDontSee('Secondary Story Item')
        ->set('dateRange', ['start' => null, 'end' => null])
        ->set('sortBy', 'most_liked')
        ->assertSeeInOrder([
            'Primary Reel Item',
            'Secondary Story Item',
            'Primary Post Item',
        ])
        ->set('sortBy', 'highest_reach')
        ->assertSeeInOrder([
            'Secondary Story Item',
            'Primary Post Item',
            'Primary Reel Item',
        ])
        ->set('sortBy', 'best_engagement')
        ->assertSeeInOrder([
            'Primary Reel Item',
            'Secondary Story Item',
            'Primary Post Item',
        ]);
});

test('content detail modal displays metrics caption permalink and linked clients', function (): void {
    $user = User::factory()->create();

    $account = InstagramAccount::factory()->for($user)->create([
        'username' => 'modalaccount',
    ]);

    $client = Client::factory()->for($user)->create([
        'name' => 'Modal Client',
    ]);

    $media = InstagramMedia::factory()->for($account)->create([
        'caption' => str_repeat('Detailed caption text ', 8),
        'permalink' => 'https://instagram.com/p/modal-post',
        'like_count' => 1234,
        'comments_count' => 222,
        'saved_count' => 45,
        'shares_count' => 12,
        'reach' => 6789,
        'impressions' => 8901,
        'engagement_rate' => 7.77,
        'media_type' => MediaType::Reel,
    ]);

    $media->clients()->attach($client->id, [
        'campaign_name' => 'Launch Campaign',
        'notes' => 'Campaign notes',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->assertSet('showDetailModal', true)
        ->assertSee('Content Details')
        ->assertSee('Detailed caption text')
        ->assertSee('View on Instagram')
        ->assertSee('Likes')
        ->assertSee('1,234')
        ->assertSee('6,789')
        ->assertSee('8,901')
        ->assertSee('7.77%')
        ->assertSee('@modalaccount')
        ->assertSee('Modal Client')
        ->assertSee('Launch Campaign')
        ->call('closeDetailModal')
        ->assertSet('showDetailModal', false);
});

test('single media can be linked to a client and duplicate links are prevented', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $client->id)
        ->set('linkCampaignName', 'Spring Launch')
        ->set('linkNotes', 'Paid collaboration')
        ->call('saveLink')
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $client->id)
        ->set('linkCampaignName', 'Spring Launch')
        ->set('linkNotes', 'Paid collaboration')
        ->call('saveLink');

    $this->assertDatabaseHas('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $media->id,
        'campaign_name' => 'Spring Launch',
        'notes' => 'Paid collaboration',
    ]);

    expect(DB::table('campaign_media')
        ->where('client_id', $client->id)
        ->where('instagram_media_id', $media->id)
        ->count())->toBe(1);
});

test('batch selection mode links all selected media to a client', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();

    $firstMedia = InstagramMedia::factory()->for($account)->create();
    $secondMedia = InstagramMedia::factory()->for($account)->create();
    $thirdMedia = InstagramMedia::factory()->for($account)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('toggleSelectionMode')
        ->call('toggleSelectedMedia', $firstMedia->id)
        ->call('toggleSelectedMedia', $secondMedia->id)
        ->call('openBatchLinkModal')
        ->set('linkClientId', (string) $client->id)
        ->set('linkCampaignName', 'Batch Campaign')
        ->call('saveLink')
        ->assertSet('selectionMode', false)
        ->assertSet('selectedMediaIds', []);

    $this->assertDatabaseHas('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $firstMedia->id,
        'campaign_name' => 'Batch Campaign',
    ]);

    $this->assertDatabaseHas('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $secondMedia->id,
        'campaign_name' => 'Batch Campaign',
    ]);

    $this->assertDatabaseMissing('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $thirdMedia->id,
    ]);
});

test('linked media can be unlinked from detail modal', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $media->clients()->attach($client->id, [
        'campaign_name' => 'To Remove',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->call('confirmUnlinkClient', $client->id)
        ->assertSet('confirmingUnlinkClientId', $client->id)
        ->call('unlinkFromClient')
        ->assertSet('confirmingUnlinkClientId', null);

    $this->assertDatabaseMissing('campaign_media', [
        'client_id' => $client->id,
        'instagram_media_id' => $media->id,
    ]);
});

test('users cannot link content to clients they do not own', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();

    $outsiderAccount = InstagramAccount::factory()->for($outsider)->create();
    $outsiderMedia = InstagramMedia::factory()->for($outsiderAccount)->create();

    Livewire::actingAs($outsider)
        ->test(Index::class)
        ->call('openDetailModal', $outsiderMedia->id)
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $ownerClient->id)
        ->set('linkCampaignName', 'Unauthorized')
        ->call('saveLink')
        ->assertHasErrors(['linkClientId']);

    $this->assertDatabaseMissing('campaign_media', [
        'client_id' => $ownerClient->id,
        'instagram_media_id' => $outsiderMedia->id,
    ]);
});

test('content gallery uses cursor pagination', function (): void {
    $user = User::factory()->create();
    $account = InstagramAccount::factory()->for($user)->create();

    foreach (range(1, 25) as $number) {
        InstagramMedia::factory()->for($account)->create([
            'caption' => 'Paged Item '.$number,
            'published_at' => now()->subMinutes($number),
        ]);
    }

    $response = $this->actingAs($user)->get(route('content.index'));

    $response->assertSuccessful()
        ->assertSee('Paged Item 1')
        ->assertSee('Paged Item 24')
        ->assertDontSee('Paged Item 25');

    $nextCursor = InstagramMedia::query()
        ->where('instagram_account_id', $account->id)
        ->orderBy('published_at', 'desc')
        ->orderByDesc('id')
        ->cursorPaginate(24, ['*'], 'cursor')
        ->nextCursor()?->encode();

    expect($nextCursor)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('content.index', ['cursor' => $nextCursor]))
        ->assertSuccessful()
        ->assertSee('Paged Item 25')
        ->assertDontSee('Paged Item 1');
});

test('content gallery shows an empty state when no media exists', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('content.index'))
        ->assertSuccessful()
        ->assertSee('No content synced yet. Connect an Instagram account and run a sync.');
});
