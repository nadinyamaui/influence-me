<?php

use App\Enums\MediaType;
use App\Livewire\Content\Index;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramMedia;
use App\Models\SocialAccount;
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

    $account = SocialAccount::factory()->for($user)->create([
        'username' => 'owneraccount',
    ]);

    $otherAccount = SocialAccount::factory()->for($otherUser)->create();

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
        ->assertSee('class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"', false)
        ->assertSee('href="'.route('content.index').'"', false);
});

test('content gallery filters and sorting options work in query layer', function (): void {
    $user = User::factory()->create();

    $primaryAccount = SocialAccount::factory()->for($user)->create();
    $secondaryAccount = SocialAccount::factory()->for($user)->create();

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

    $account = SocialAccount::factory()->for($user)->create([
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

    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Launch Campaign',
    ]);

    $campaign->instagramMedia()->attach($media->id, [
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
        ->assertSee('href="'.route('clients.show', $client).'"', false)
        ->call('closeDetailModal')
        ->assertSet('showDetailModal', false);
});

test('content detail modal can open from media query parameter', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create([
        'caption' => 'Deep link content caption',
    ]);

    Livewire::withQueryParams(['media' => (string) $media->id])
        ->actingAs($user)
        ->test(Index::class)
        ->assertSet('selectedMediaId', $media->id)
        ->assertSet('showDetailModal', true)
        ->assertSee('Content Details')
        ->assertSee('Deep link content caption');
});

test('content detail modal shows 90-day account average comparisons and campaign context', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create([
        'username' => 'analyticsmodal',
    ]);
    $client = Client::factory()->for($user)->create([
        'name' => 'Comparison Client',
    ]);
    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Comparison Campaign',
    ]);

    $selectedMedia = InstagramMedia::factory()->for($account)->create([
        'caption' => 'Selected comparison media',
        'published_at' => now()->subDays(5),
        'like_count' => 150,
        'comments_count' => 15,
        'reach' => 1500,
        'engagement_rate' => 6.00,
    ]);

    $recentMedia = InstagramMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(12),
        'like_count' => 100,
        'comments_count' => 10,
        'reach' => 1000,
        'engagement_rate' => 4.00,
    ]);

    $oldOutlierMedia = InstagramMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(120),
        'like_count' => 1000,
        'comments_count' => 100,
        'reach' => 10000,
        'engagement_rate' => 40.00,
    ]);

    $campaign->instagramMedia()->attach($selectedMedia->id);
    $campaign->instagramMedia()->attach($recentMedia->id);
    $campaign->instagramMedia()->attach($oldOutlierMedia->id);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $selectedMedia->id)
        ->assertSee('↑ 20% above average')
        ->assertSee('Account avg: 5.00%')
        ->assertSee('Comparison Client')
        ->assertSee('Comparison Campaign')
        ->assertSee('Part of campaign with 2 other posts')
        ->assertSee('href="'.route('clients.show', $client).'"', false);
});

test('content detail media query ignores media outside authenticated ownership', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherAccount = SocialAccount::factory()->for($otherUser)->create();
    $otherMedia = InstagramMedia::factory()->for($otherAccount)->create([
        'caption' => 'Outsider deep link content',
    ]);

    Livewire::withQueryParams(['media' => (string) $otherMedia->id])
        ->actingAs($user)
        ->test(Index::class)
        ->assertSet('selectedMediaId', null)
        ->assertSet('showDetailModal', false)
        ->assertDontSee('Outsider deep link content');
});

test('single media cannot be linked to the same campaign twice', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();
    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Spring Launch',
    ]);
    $media = InstagramMedia::factory()->for($account)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $client->id)
        ->set('linkCampaignId', (string) $campaign->id)
        ->set('linkNotes', 'Paid collaboration')
        ->call('saveLink')
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $client->id)
        ->set('linkCampaignId', (string) $campaign->id)
        ->set('linkNotes', 'Paid collaboration')
        ->call('saveLink')
        ->assertHasErrors(['linkCampaignId']);

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $media->id,
        'notes' => 'Paid collaboration',
    ]);

    expect(DB::table('campaign_media')
        ->where('campaign_id', $campaign->id)
        ->where('instagram_media_id', $media->id)
        ->count())->toBe(1);
});

test('campaign options refresh when link client changes in the link modal', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $clientA = Client::factory()->for($user)->create(['name' => 'Client A']);
    $clientB = Client::factory()->for($user)->create(['name' => 'Client B']);

    $campaignA = Campaign::factory()->for($clientA)->create(['name' => 'Campaign A']);
    $campaignB = Campaign::factory()->for($clientB)->create(['name' => 'Campaign B']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $clientA->id)
        ->assertSee('Campaign A')
        ->assertDontSee('Campaign B')
        ->set('linkCampaignId', (string) $campaignA->id)
        ->set('linkClientId', (string) $clientB->id)
        ->assertSet('linkCampaignId', null)
        ->assertSee('Campaign B')
        ->assertDontSee('Campaign A');
});

test('batch selection mode links all selected media to a client', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();
    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Batch Campaign',
    ]);

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
        ->set('linkCampaignId', (string) $campaign->id)
        ->call('saveLink')
        ->assertSet('selectionMode', false)
        ->assertSet('selectedMediaIds', []);

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $firstMedia->id,
    ]);

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $secondMedia->id,
    ]);

    $this->assertDatabaseMissing('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $thirdMedia->id,
    ]);
});

test('linked media can be unlinked from detail modal', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'To Remove',
    ]);
    $campaign->instagramMedia()->attach($media->id);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->call('unlinkFromClient', $client->id);

    $this->assertDatabaseMissing('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $media->id,
    ]);
});

test('users cannot link content to clients they do not own', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $ownerCampaign = Campaign::factory()->for($ownerClient)->create();

    $outsiderAccount = SocialAccount::factory()->for($outsider)->create();
    $outsiderMedia = InstagramMedia::factory()->for($outsiderAccount)->create();

    Livewire::actingAs($outsider)
        ->test(Index::class)
        ->call('openDetailModal', $outsiderMedia->id)
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $ownerClient->id)
        ->set('linkCampaignId', (string) $ownerCampaign->id)
        ->call('saveLink')
        ->assertHasErrors(['linkClientId', 'linkCampaignId']);

    $this->assertDatabaseMissing('campaign_media', [
        'instagram_media_id' => $outsiderMedia->id,
    ]);
});

test('inline campaign creation works in content linking flow', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();
    $client = Client::factory()->for($user)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('openDetailModal', $media->id)
        ->call('openSingleLinkModal')
        ->set('linkClientId', (string) $client->id)
        ->call('toggleInlineCampaignForm')
        ->set('campaignForm.name', 'Inline Launch Campaign')
        ->set('campaignForm.description', 'Created from link flow')
        ->call('createInlineCampaign')
        ->call('saveLink');

    $campaign = Campaign::query()
        ->where('client_id', $client->id)
        ->where('name', 'Inline Launch Campaign')
        ->first();

    expect($campaign)->not->toBeNull();

    $this->assertDatabaseHas('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $media->id,
    ]);
});

test('content gallery uses cursor pagination', function (): void {
    $user = User::factory()->create();
    $account = SocialAccount::factory()->for($user)->create();

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
        ->where('social_account_id', $account->id)
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
