<?php

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Enums\ScheduledPostStatus;
use App\Livewire\Proposals\Create;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from the proposal create page', function (): void {
    $this->get(route('proposals.create'))
        ->assertRedirect(route('login'));
});

test('authenticated users can render the proposal create page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('proposals.create'))
        ->assertSuccessful()
        ->assertSee('New Proposal')
        ->assertSee('Title')
        ->assertSee('Client')
        ->assertSee('Content')
        ->assertSee('Campaigns')
        ->assertSee('Save as Draft');
});

test('create page shows client dropdown scoped to user', function (): void {
    $user = User::factory()->create();
    $ownClient = Client::factory()->for($user)->create(['name' => 'My Client']);
    $otherClient = Client::factory()->create(['name' => 'Other Client']);

    $this->actingAs($user)
        ->get(route('proposals.create'))
        ->assertSee('My Client')
        ->assertDontSee('Other Client');
});

test('create page shows instagram account dropdown scoped to user', function (): void {
    $user = User::factory()->create();
    $ownAccount = InstagramAccount::factory()->for($user)->create(['username' => 'my_account']);
    $otherAccount = InstagramAccount::factory()->create(['username' => 'other_account']);

    $this->actingAs($user)
        ->get(route('proposals.create'))
        ->assertSee('my_account')
        ->assertDontSee('other_account');
});

test('authenticated users can create a draft proposal with campaigns and scheduled content', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Summer Campaign Proposal')
        ->set('form.client_id', (string) $client->id)
        ->set('form.content', '# Overview\n\nThis is the proposal content.')
        ->set('form.campaigns.0.name', 'Summer Launch')
        ->set('form.campaigns.0.description', 'A summer product launch campaign')
        ->set('form.campaigns.0.scheduled_items.0.title', 'Launch Post')
        ->set('form.campaigns.0.scheduled_items.0.media_type', MediaType::Post->value)
        ->set('form.campaigns.0.scheduled_items.0.instagram_account_id', (string) $account->id)
        ->set('form.campaigns.0.scheduled_items.0.scheduled_at', '2026-06-01T10:00')
        ->set('form.campaigns.0.scheduled_items.0.description', 'First launch post')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('proposals.index'));

    $proposal = Proposal::query()->first();

    expect($proposal)->not->toBeNull()
        ->and($proposal->user_id)->toBe($user->id)
        ->and($proposal->client_id)->toBe($client->id)
        ->and($proposal->title)->toBe('Summer Campaign Proposal')
        ->and($proposal->status)->toBe(ProposalStatus::Draft);

    expect($proposal->campaigns)->toHaveCount(1);

    $campaign = $proposal->campaigns->first();
    expect($campaign->name)->toBe('Summer Launch')
        ->and($campaign->client_id)->toBe($client->id);

    $scheduledPosts = $campaign->scheduledPosts;
    expect($scheduledPosts)->toHaveCount(1);

    $post = $scheduledPosts->first();
    expect($post->title)->toBe('Launch Post')
        ->and($post->media_type)->toBe(MediaType::Post)
        ->and($post->instagram_account_id)->toBe($account->id)
        ->and($post->user_id)->toBe($user->id)
        ->and($post->client_id)->toBe($client->id)
        ->and($post->status)->toBe(ScheduledPostStatus::Planned);
});

test('proposal requires at least one campaign', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Test Proposal')
        ->set('form.client_id', (string) $client->id)
        ->set('form.content', 'Some content')
        ->set('form.campaigns', [])
        ->call('save')
        ->assertHasErrors(['form.campaigns']);

    $this->assertDatabaseCount('proposals', 0);
});

test('each campaign requires at least one scheduled content item', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Test Proposal')
        ->set('form.client_id', (string) $client->id)
        ->set('form.content', 'Some content')
        ->set('form.campaigns.0.name', 'Campaign One')
        ->set('form.campaigns.0.scheduled_items', [])
        ->call('save')
        ->assertHasErrors(['form.campaigns.0.scheduled_items']);

    $this->assertDatabaseCount('proposals', 0);
});

test('scheduled content enforces valid media type', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Test Proposal')
        ->set('form.client_id', (string) $client->id)
        ->set('form.content', 'Some content')
        ->set('form.campaigns.0.name', 'Campaign One')
        ->set('form.campaigns.0.scheduled_items.0.title', 'Test Post')
        ->set('form.campaigns.0.scheduled_items.0.media_type', 'invalid_type')
        ->set('form.campaigns.0.scheduled_items.0.instagram_account_id', (string) $account->id)
        ->set('form.campaigns.0.scheduled_items.0.scheduled_at', '2026-06-01T10:00')
        ->call('save')
        ->assertHasErrors(['form.campaigns.0.scheduled_items.0.media_type']);
});

test('client dropdown is scoped to authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherClient = Client::factory()->for($otherUser)->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Test Proposal')
        ->set('form.client_id', (string) $otherClient->id)
        ->set('form.content', 'Some content')
        ->set('form.campaigns.0.name', 'Campaign')
        ->set('form.campaigns.0.scheduled_items.0.title', 'Post')
        ->set('form.campaigns.0.scheduled_items.0.media_type', MediaType::Post->value)
        ->set('form.campaigns.0.scheduled_items.0.instagram_account_id', '999')
        ->set('form.campaigns.0.scheduled_items.0.scheduled_at', '2026-06-01T10:00')
        ->call('save')
        ->assertHasErrors(['form.client_id']);

    $this->assertDatabaseCount('proposals', 0);
});

test('instagram account is scoped to authenticated user', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $otherAccount = InstagramAccount::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Test Proposal')
        ->set('form.client_id', (string) $client->id)
        ->set('form.content', 'Some content')
        ->set('form.campaigns.0.name', 'Campaign')
        ->set('form.campaigns.0.scheduled_items.0.title', 'Post')
        ->set('form.campaigns.0.scheduled_items.0.media_type', MediaType::Post->value)
        ->set('form.campaigns.0.scheduled_items.0.instagram_account_id', (string) $otherAccount->id)
        ->set('form.campaigns.0.scheduled_items.0.scheduled_at', '2026-06-01T10:00')
        ->call('save')
        ->assertHasErrors(['form.campaigns.0.scheduled_items.0.instagram_account_id']);
});

test('create form validates required fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', '')
        ->set('form.client_id', '')
        ->set('form.content', '')
        ->set('form.campaigns', [])
        ->call('save')
        ->assertHasErrors([
            'form.title',
            'form.client_id',
            'form.content',
            'form.campaigns',
        ]);
});

test('markdown preview toggle works on create page', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->assertSet('previewing', false)
        ->call('togglePreview')
        ->assertSet('previewing', true)
        ->call('togglePreview')
        ->assertSet('previewing', false);
});

test('can add and remove campaigns', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Create::class)
        ->assertCount('form.campaigns', 1)
        ->call('addCampaign')
        ->assertCount('form.campaigns', 2)
        ->call('removeCampaign', 1)
        ->assertCount('form.campaigns', 1);
});

test('cannot remove last campaign', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->assertCount('form.campaigns', 1)
        ->call('removeCampaign', 0)
        ->assertCount('form.campaigns', 1);
});

test('can add and remove scheduled items within a campaign', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->assertCount('form.campaigns.0.scheduled_items', 1)
        ->call('addScheduledItem', 0)
        ->assertCount('form.campaigns.0.scheduled_items', 2)
        ->call('removeScheduledItem', 0, 1)
        ->assertCount('form.campaigns.0.scheduled_items', 1);
});

test('cannot remove last scheduled item from a campaign', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->assertCount('form.campaigns.0.scheduled_items', 1)
        ->call('removeScheduledItem', 0, 0)
        ->assertCount('form.campaigns.0.scheduled_items', 1);
});

test('create page cancel button links to proposals index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('proposals.create'))
        ->assertSee('href="'.route('proposals.index').'"', false);
});

test('proposal create with multiple campaigns and items', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('form.title', 'Multi Campaign Proposal')
        ->set('form.client_id', (string) $client->id)
        ->set('form.content', 'Content here')
        ->set('form.campaigns.0.name', 'Campaign A')
        ->set('form.campaigns.0.scheduled_items.0.title', 'Post A1')
        ->set('form.campaigns.0.scheduled_items.0.media_type', MediaType::Post->value)
        ->set('form.campaigns.0.scheduled_items.0.instagram_account_id', (string) $account->id)
        ->set('form.campaigns.0.scheduled_items.0.scheduled_at', '2026-06-01T10:00')
        ->call('addScheduledItem', 0)
        ->set('form.campaigns.0.scheduled_items.1.title', 'Reel A2')
        ->set('form.campaigns.0.scheduled_items.1.media_type', MediaType::Reel->value)
        ->set('form.campaigns.0.scheduled_items.1.instagram_account_id', (string) $account->id)
        ->set('form.campaigns.0.scheduled_items.1.scheduled_at', '2026-06-02T14:00')
        ->call('addCampaign')
        ->set('form.campaigns.1.name', 'Campaign B')
        ->set('form.campaigns.1.scheduled_items.0.title', 'Story B1')
        ->set('form.campaigns.1.scheduled_items.0.media_type', MediaType::Story->value)
        ->set('form.campaigns.1.scheduled_items.0.instagram_account_id', (string) $account->id)
        ->set('form.campaigns.1.scheduled_items.0.scheduled_at', '2026-06-03T09:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('proposals.index'));

    $proposal = Proposal::query()->first();
    expect($proposal->campaigns)->toHaveCount(2);
    expect($proposal->campaigns->first()->scheduledPosts)->toHaveCount(2);
    expect($proposal->campaigns->last()->scheduledPosts)->toHaveCount(1);
});

test('index page new proposal button links to create page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSee('href="'.route('proposals.create').'"', false);
});
