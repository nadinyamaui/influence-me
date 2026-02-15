<?php

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Livewire\Proposals\Edit;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from the proposal edit page', function (): void {
    $proposal = Proposal::factory()->draft()->create();

    $this->get(route('proposals.edit', $proposal))
        ->assertRedirect(route('login'));
});

test('edit page renders with prefilled data for the owner of a draft proposal', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create(['name' => 'Acme Corp']);
    $account = InstagramAccount::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create([
        'title' => 'Summer Proposal',
        'content' => '# Summer Plan',
    ]);

    $campaign = Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
        'name' => 'Summer Launch',
    ]);

    ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Launch Post',
        'media_type' => MediaType::Post,
    ]);

    $this->actingAs($user)
        ->get(route('proposals.edit', $proposal))
        ->assertSuccessful()
        ->assertSee('Edit Proposal')
        ->assertSee('Summer Proposal');
});

test('non-owners cannot access proposal edit page', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $proposal = Proposal::factory()->for($owner)->draft()->create();

    $this->actingAs($outsider)
        ->get(route('proposals.edit', $proposal))
        ->assertForbidden();
});

test('edit page loads existing campaign and scheduled content data', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create([
        'title' => 'Test Proposal',
    ]);

    $campaign = Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
        'name' => 'First Campaign',
        'description' => 'Campaign description',
    ]);

    ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Scheduled Reel',
        'media_type' => MediaType::Reel,
        'scheduled_at' => '2026-07-01 10:00:00',
    ]);

    $component = Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal]);

    expect($component->get('form.title'))->toBe('Test Proposal')
        ->and($component->get('form.client_id'))->toBe((string) $client->id)
        ->and($component->get('form.campaigns'))->toHaveCount(1)
        ->and($component->get('form.campaigns.0.name'))->toBe('First Campaign')
        ->and($component->get('form.campaigns.0.description'))->toBe('Campaign description')
        ->and($component->get('form.campaigns.0.scheduled_items'))->toHaveCount(1)
        ->and($component->get('form.campaigns.0.scheduled_items.0.title'))->toBe('Scheduled Reel')
        ->and($component->get('form.campaigns.0.scheduled_items.0.media_type'))->toBe(MediaType::Reel->value);
});

test('owners can update draft proposal with campaigns and scheduled content', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create([
        'title' => 'Old Title',
    ]);

    $oldCampaign = Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
        'name' => 'Old Campaign',
    ]);

    ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'campaign_id' => $oldCampaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Old Post',
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal])
        ->set('form.title', 'New Title')
        ->set('form.content', 'Updated content')
        ->set('form.campaigns.0.name', 'New Campaign')
        ->set('form.campaigns.0.scheduled_items.0.title', 'New Post')
        ->set('form.campaigns.0.scheduled_items.0.media_type', MediaType::Reel->value)
        ->set('form.campaigns.0.scheduled_items.0.instagram_account_id', (string) $account->id)
        ->set('form.campaigns.0.scheduled_items.0.scheduled_at', '2026-08-01T10:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('proposals.index'));

    $updated = $proposal->fresh();
    expect($updated->title)->toBe('New Title')
        ->and($updated->content)->toBe('Updated content');

    $campaigns = $updated->campaigns;
    expect($campaigns)->toHaveCount(1)
        ->and($campaigns->first()->name)->toBe('New Campaign');

    $posts = $campaigns->first()->scheduledPosts;
    expect($posts)->toHaveCount(1)
        ->and($posts->first()->title)->toBe('New Post')
        ->and($posts->first()->media_type)->toBe(MediaType::Reel);

    // Old campaign and post should be deleted
    $this->assertDatabaseMissing('campaigns', ['id' => $oldCampaign->id]);
});

test('revised proposals are editable', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->revised()->create();

    Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
        'name' => 'Revised Campaign',
    ]);

    $this->actingAs($user)
        ->get(route('proposals.edit', $proposal))
        ->assertSuccessful()
        ->assertSee('Edit Proposal')
        ->assertSee('Save');
});

test('sent proposals show read-only view with duplicate option', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->sent()->create([
        'title' => 'Sent Proposal Title',
    ]);

    $this->actingAs($user)
        ->get(route('proposals.edit', $proposal))
        ->assertSuccessful()
        ->assertSee('sent and cannot be edited')
        ->assertSee('Duplicate')
        ->assertSee('Sent Proposal Title');
});

test('approved proposals show read-only view', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->approved()->create();

    $this->actingAs($user)
        ->get(route('proposals.edit', $proposal))
        ->assertSuccessful()
        ->assertSee('approved and cannot be edited')
        ->assertSee('Duplicate');
});

test('rejected proposals show read-only view', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->rejected()->create();

    $this->actingAs($user)
        ->get(route('proposals.edit', $proposal))
        ->assertSuccessful()
        ->assertSee('rejected and cannot be edited')
        ->assertSee('Duplicate');
});

test('delete with confirmation works on edit page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    $campaign = Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal])
        ->call('confirmDelete')
        ->assertSet('confirmingDelete', true)
        ->call('delete')
        ->assertRedirect(route('proposals.index'));

    $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
});

test('duplicate creates a new draft proposal', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->sent()->create([
        'title' => 'Original Proposal',
        'content' => 'Original content',
    ]);

    $campaign = Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
        'name' => 'Original Campaign',
    ]);

    ScheduledPost::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Original Post',
        'media_type' => MediaType::Post,
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal])
        ->call('duplicate')
        ->assertHasNoErrors();

    $newProposal = Proposal::where('title', 'Original Proposal (Copy)')->first();

    expect($newProposal)->not->toBeNull()
        ->and($newProposal->id)->not->toBe($proposal->id)
        ->and($newProposal->status)->toBe(ProposalStatus::Draft)
        ->and($newProposal->content)->toBe('Original content');
});

test('edit form validates required fields', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    Campaign::factory()->create([
        'client_id' => $client->id,
        'proposal_id' => $proposal->id,
        'name' => 'Test Campaign',
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal])
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

test('edit page markdown preview toggle works', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal])
        ->assertSet('previewing', false)
        ->call('togglePreview')
        ->assertSet('previewing', true)
        ->call('togglePreview')
        ->assertSet('previewing', false);
});

test('edit page cancel delete resets state', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    Livewire::actingAs($user)
        ->test(Edit::class, ['proposal' => $proposal])
        ->call('confirmDelete')
        ->assertSet('confirmingDelete', true)
        ->call('cancelDelete')
        ->assertSet('confirmingDelete', false);
});

test('index page draft proposal edit link points to edit route', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSee(route('proposals.edit', $proposal));
});
