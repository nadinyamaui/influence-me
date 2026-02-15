<?php

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Livewire\Schedule\Index;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from schedule page', function (): void {
    $this->get(route('schedule.index'))
        ->assertRedirect(route('login'));
});

test('owner can view schedule timeline grouped by day with campaign and proposal context', function (): void {
    $owner = User::factory()->create();

    $client = Client::factory()->for($owner)->create([
        'name' => 'Acme Client',
    ]);

    $account = InstagramAccount::factory()->for($owner)->create([
        'username' => 'owneraccount',
    ]);

    $proposal = Proposal::factory()->for($owner)->for($client)->create([
        'status' => ProposalStatus::Sent,
    ]);

    $campaign = Campaign::factory()->for($client)->for($proposal)->create([
        'name' => 'Launch Campaign',
    ]);

    ScheduledPost::factory()->for($owner)->create([
        'client_id' => $client->id,
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Morning Launch Story',
        'description' => 'Campaign schedule details',
        'media_type' => MediaType::Story,
        'status' => ScheduledPostStatus::Planned,
        'scheduled_at' => now()->addDay()->setTime(10, 0),
    ]);

    ScheduledPost::factory()->for($owner)->create([
        'client_id' => null,
        'campaign_id' => null,
        'instagram_account_id' => $account->id,
        'title' => 'Follow-up Post',
        'media_type' => MediaType::Post,
        'status' => ScheduledPostStatus::Published,
        'scheduled_at' => now()->addDays(2)->setTime(15, 0),
    ]);

    $this->actingAs($owner)
        ->get(route('schedule.index'))
        ->assertSuccessful()
        ->assertSee('Schedule')
        ->assertSee('Morning Launch Story')
        ->assertSee('Launch Campaign')
        ->assertSee('Proposal Sent')
        ->assertSee('@owneraccount')
        ->assertSee('Follow-up Post')
        ->assertSee('No client')
        ->assertSee('No campaign');
});

test('schedule filters are applied in query layer', function (): void {
    $owner = User::factory()->create();

    $clientA = Client::factory()->for($owner)->create();
    $clientB = Client::factory()->for($owner)->create();

    $accountA = InstagramAccount::factory()->for($owner)->create();
    $accountB = InstagramAccount::factory()->for($owner)->create();

    $campaignA = Campaign::factory()->for($clientA)->create(['name' => 'Campaign A']);
    $campaignB = Campaign::factory()->for($clientB)->create(['name' => 'Campaign B']);

    ScheduledPost::factory()->for($owner)->create([
        'client_id' => $clientA->id,
        'campaign_id' => $campaignA->id,
        'instagram_account_id' => $accountA->id,
        'title' => 'Filtered Target',
        'media_type' => MediaType::Reel,
        'status' => ScheduledPostStatus::Planned,
        'scheduled_at' => now()->addDays(3)->setTime(11, 0),
    ]);

    ScheduledPost::factory()->for($owner)->create([
        'client_id' => $clientB->id,
        'campaign_id' => $campaignB->id,
        'instagram_account_id' => $accountB->id,
        'title' => 'Filtered Out',
        'media_type' => MediaType::Story,
        'status' => ScheduledPostStatus::Cancelled,
        'scheduled_at' => now()->addDays(5)->setTime(11, 0),
    ]);

    Livewire::actingAs($owner)
        ->test(Index::class)
        ->set('statusFilter', ScheduledPostStatus::Planned->value)
        ->set('clientFilter', (string) $clientA->id)
        ->set('accountFilter', (string) $accountA->id)
        ->set('campaignFilter', (string) $campaignA->id)
        ->set('mediaTypeFilter', MediaType::Reel->value)
        ->set('dateFrom', now()->addDays(2)->format('Y-m-d'))
        ->set('dateTo', now()->addDays(4)->format('Y-m-d'))
        ->assertSee('Filtered Target')
        ->assertDontSee('Filtered Out');
});

test('owner can create edit mark status and delete scheduled post', function (): void {
    $owner = User::factory()->create();

    $client = Client::factory()->for($owner)->create();
    $account = InstagramAccount::factory()->for($owner)->create();
    $campaign = Campaign::factory()->for($client)->create();

    $test = Livewire::actingAs($owner)
        ->test(Index::class)
        ->call('openCreateModal')
        ->set('title', 'New Schedule Item')
        ->set('description', 'A planned description')
        ->set('clientId', (string) $client->id)
        ->set('campaignId', (string) $campaign->id)
        ->set('mediaType', MediaType::Post->value)
        ->set('instagramAccountId', (string) $account->id)
        ->set('scheduledAt', now()->addDays(2)->format('Y-m-d\\TH:i'))
        ->set('status', ScheduledPostStatus::Planned->value)
        ->call('savePost');

    $post = ScheduledPost::query()->where('title', 'New Schedule Item')->first();

    expect($post)->not->toBeNull();

    $test->call('openEditModal', $post->id)
        ->set('title', 'Updated Schedule Item')
        ->set('status', ScheduledPostStatus::Cancelled->value)
        ->call('savePost')
        ->call('markPublished', $post->id)
        ->call('confirmDelete', $post->id)
        ->call('deletePost');

    $this->assertDatabaseMissing('scheduled_posts', [
        'id' => $post->id,
    ]);
});

test('non-owners cannot access schedule page', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $client = Client::factory()->for($owner)->create();
    $account = InstagramAccount::factory()->for($owner)->create();

    ScheduledPost::factory()->for($owner)->create([
        'client_id' => $client->id,
        'instagram_account_id' => $account->id,
        'title' => 'Owner Only',
        'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($outsider)
        ->get(route('schedule.index'))
        ->assertSuccessful()
        ->assertDontSee('Owner Only');
});
