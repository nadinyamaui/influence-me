<?php

use App\Enums\ProposalStatus;
use App\Livewire\Proposals\Create as ProposalCreate;
use App\Livewire\Proposals\Edit as ProposalEdit;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Livewire\Livewire;

function proposalPayload(int $clientId, int $instagramAccountId): array
{
    return [
        'title' => 'Q2 Campaign Proposal',
        'client_id' => (string) $clientId,
        'content' => "# Proposal\n\nCampaign scope in markdown.",
        'campaigns' => [[
            'id' => null,
            'name' => 'Spring Launch',
            'description' => 'Campaign plan details',
            'scheduled_items' => [[
                'title' => 'Teaser Reel',
                'description' => 'Countdown to launch',
                'media_type' => 'reel',
                'instagram_account_id' => (string) $instagramAccountId,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ]],
        ]],
    ];
}

test('guests are redirected from create and edit proposal pages', function (): void {
    $proposal = Proposal::factory()->create();

    $this->get(route('proposals.create'))
        ->assertRedirect(route('login'));

    $this->get(route('proposals.edit', $proposal))
        ->assertRedirect(route('login'));
});

test('authenticated influencer can create a draft proposal with campaign and scheduled content', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $payload = proposalPayload($client->id, $account->id);

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('title', $payload['title'])
        ->set('client_id', $payload['client_id'])
        ->set('content', $payload['content'])
        ->set('campaigns', $payload['campaigns'])
        ->call('save')
        ->assertRedirect(route('proposals.index'));

    $proposal = Proposal::query()->where('user_id', $user->id)->first();

    expect($proposal)->not->toBeNull()
        ->and($proposal->status)->toBe(ProposalStatus::Draft)
        ->and($proposal->client_id)->toBe($client->id);

    $campaign = Campaign::query()->where('proposal_id', $proposal->id)->first();

    expect($campaign)->not->toBeNull()
        ->and($campaign->name)->toBe('Spring Launch');

    $scheduledPost = ScheduledPost::query()->where('campaign_id', $campaign->id)->first();

    expect($scheduledPost)->not->toBeNull()
        ->and($scheduledPost->title)->toBe('Teaser Reel')
        ->and($scheduledPost->media_type->value)->toBe('reel')
        ->and($scheduledPost->instagram_account_id)->toBe($account->id);
});

test('linking an existing campaign on create does not remove existing scheduled posts', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $existingCampaign = Campaign::factory()->for($client)->create([
        'proposal_id' => null,
        'name' => 'Evergreen Campaign',
    ]);

    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $existingCampaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Existing Scheduled Post',
    ]);

    $payload = proposalPayload($client->id, $account->id);
    $payload['campaigns'][0]['id'] = $existingCampaign->id;

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('title', $payload['title'])
        ->set('client_id', $payload['client_id'])
        ->set('content', $payload['content'])
        ->set('campaigns', $payload['campaigns'])
        ->call('save')
        ->assertRedirect(route('proposals.index'));

    expect(ScheduledPost::query()->where('campaign_id', $existingCampaign->id)->count())->toBe(2);
});

test('create page enforces scoped ownership validation for client campaign and instagram account', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $client = Client::factory()->for($user)->create();
    $otherClient = Client::factory()->for($otherUser)->create();

    $account = InstagramAccount::factory()->for($user)->create();
    $otherAccount = InstagramAccount::factory()->for($otherUser)->create();

    $otherCampaign = Campaign::factory()->for($otherClient)->create();

    $payload = proposalPayload($otherClient->id, $otherAccount->id);
    $payload['campaigns'][0]['id'] = $otherCampaign->id;

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('title', $payload['title'])
        ->set('client_id', $payload['client_id'])
        ->set('content', $payload['content'])
        ->set('campaigns', $payload['campaigns'])
        ->call('save')
        ->assertHasErrors([
            'client_id',
            'campaigns.0.id',
            'campaigns.0.scheduled_items.0.instagram_account_id',
        ]);

    $validPayload = proposalPayload($client->id, $account->id);
    $validPayload['campaigns'] = [];

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('title', $validPayload['title'])
        ->set('client_id', $validPayload['client_id'])
        ->set('content', $validPayload['content'])
        ->set('campaigns', $validPayload['campaigns'])
        ->call('save')
        ->assertHasErrors(['campaigns']);
});

test('markdown preview toggle renders proposal markdown on create page', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('content', '# Heading')
        ->call('togglePreview')
        ->assertSet('previewMode', true)
        ->assertSeeHtml('<h1>Heading</h1>');
});

test('edit page loads existing proposal data and updates draft proposals', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create([
        'title' => 'Original Title',
        'content' => '# Original',
    ]);

    $campaign = Campaign::factory()->for($client)->create([
        'proposal_id' => $proposal->id,
        'name' => 'Original Campaign',
    ]);

    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Original Post',
        'media_type' => 'post',
    ]);

    $component = Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal]);

    $component
        ->assertSet('title', 'Original Title')
        ->assertSet('client_id', (string) $client->id)
        ->assertSet('campaigns.0.id', $campaign->id)
        ->assertSet('campaigns.0.scheduled_items.0.title', 'Original Post')
        ->set('title', 'Updated Title')
        ->set('campaigns.0.scheduled_items.0.media_type', 'story')
        ->call('update')
        ->assertRedirect(route('proposals.index'));

    $proposal->refresh();

    expect($proposal->title)->toBe('Updated Title');

    $updatedScheduledPost = ScheduledPost::query()->where('campaign_id', $campaign->id)->first();

    expect($updatedScheduledPost->media_type->value)->toBe('story');
});

test('sent proposals render read-only state and can be duplicated', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->sent()->create([
        'title' => 'Sent Proposal',
        'content' => '# Locked Content',
    ]);

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->assertSee('This proposal is read-only')
        ->assertSee('Duplicate')
        ->call('duplicate')
        ->assertRedirect();

    $duplicate = Proposal::query()
        ->where('user_id', $user->id)
        ->where('title', 'Sent Proposal (Copy)')
        ->first();

    expect($duplicate)->not->toBeNull()
        ->and($duplicate->status)->toBe(ProposalStatus::Draft);
});

test('users cannot update sent proposals', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->sent()->create();

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->call('update')
        ->assertForbidden();
});

test('users cannot access proposal edit page for another user proposal', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerClient = Client::factory()->for($owner)->create();
    $proposal = Proposal::factory()->for($owner)->for($ownerClient)->create();

    $this->actingAs($outsider)
        ->get(route('proposals.edit', $proposal))
        ->assertForbidden();
});

test('users can delete proposals from edit page with confirmation', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->create();

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->call('confirmDelete')
        ->assertSet('confirmingDelete', true)
        ->call('delete')
        ->assertRedirect(route('proposals.index'));

    $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
});
