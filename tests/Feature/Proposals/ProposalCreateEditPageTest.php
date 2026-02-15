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

function proposalWorkflowPayload(int $instagramAccountId): array
{
    return [
        'title' => 'Q2 Campaign Proposal',
        'content' => "# Proposal\n\nCampaign scope in markdown.",
        'campaigns' => [[
            'id' => null,
            'name' => 'Spring Launch',
            'description' => 'Campaign plan details',
        ]],
        'scheduledItems' => [[
            'id' => null,
            'campaign_index' => 0,
            'title' => 'Teaser Reel',
            'description' => 'Countdown to launch',
            'media_type' => 'reel',
            'instagram_account_id' => (string) $instagramAccountId,
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
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

test('authenticated influencer can create an initial draft proposal and continue to the step flow', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('title', 'Q2 Campaign Proposal')
        ->set('client_id', (string) $client->id)
        ->call('save')
        ->assertRedirect();

    $proposal = Proposal::query()->where('user_id', $user->id)->first();

    expect($proposal)->not->toBeNull()
        ->and($proposal->status)->toBe(ProposalStatus::Draft)
        ->and($proposal->client_id)->toBe($client->id)
        ->and($proposal->content)->toBe('');
});

test('create page enforces scoped ownership validation for client selection', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherClient = Client::factory()->for($otherUser)->create();

    Livewire::actingAs($user)
        ->test(ProposalCreate::class)
        ->set('title', 'Scoped Proposal')
        ->set('client_id', (string) $otherClient->id)
        ->call('save')
        ->assertHasErrors(['client_id']);
});

test('edit page loads existing proposal data and updates draft proposals through stepped payload', function (): void {
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

    $payload = proposalWorkflowPayload($account->id);
    $payload['campaigns'][0]['id'] = $campaign->id;

    $component = Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal]);

    $component
        ->assertSet('title', 'Original Title')
        ->assertSet('client_id', (string) $client->id)
        ->assertSet('campaigns.0.id', $campaign->id)
        ->assertSet('scheduledItems.0.title', 'Original Post')
        ->set('title', 'Updated Title')
        ->set('content', $payload['content'])
        ->set('campaigns', $payload['campaigns'])
        ->set('scheduledItems', $payload['scheduledItems'])
        ->call('update')
        ->assertRedirect(route('proposals.index'));

    $proposal->refresh();

    expect($proposal->title)->toBe('Updated Title');

    $updatedCampaign = Campaign::query()->where('proposal_id', $proposal->id)->first();
    $updatedScheduledPost = ScheduledPost::query()->where('campaign_id', $updatedCampaign->id)->first();

    expect($updatedCampaign->name)->toBe('Spring Launch')
        ->and($updatedScheduledPost)->not->toBeNull()
        ->and($updatedScheduledPost->media_type->value)->toBe('reel');
});

test('linking an existing campaign on edit is ignored while saving draft', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    $existingCampaign = Campaign::factory()->for($client)->create([
        'proposal_id' => null,
        'name' => 'Evergreen Campaign',
    ]);

    $existingScheduledPost = ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $existingCampaign->id,
        'instagram_account_id' => $account->id,
        'title' => 'Existing Scheduled Post',
    ]);

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->set('campaigns', [[
            'id' => $existingCampaign->id,
            'name' => 'Evergreen Campaign',
            'description' => '',
        ]])
        ->set('scheduledItems', [
            [
                'id' => $existingScheduledPost->id,
                'campaign_index' => 0,
                'title' => $existingScheduledPost->title,
                'description' => $existingScheduledPost->description ?? '',
                'media_type' => $existingScheduledPost->media_type->value,
                'instagram_account_id' => (string) $existingScheduledPost->instagram_account_id,
                'scheduled_at' => $existingScheduledPost->scheduled_at->format('Y-m-d\TH:i'),
            ],
            [
                'id' => null,
                'campaign_index' => 0,
                'title' => 'New Scheduled Post',
                'description' => '',
                'media_type' => 'post',
                'instagram_account_id' => (string) $account->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ],
        ])
        ->call('update')
        ->assertRedirect(route('proposals.index'));

    expect(ScheduledPost::query()->where('campaign_id', $existingCampaign->id)->count())->toBe(1);
});

test('saving as draft skips validation and persists safe defaults', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create([
        'title' => 'Original Title',
        'content' => '# Original',
    ]);

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->set('title', '')
        ->set('client_id', '')
        ->set('content', '')
        ->set('campaigns', [[
            'id' => null,
            'name' => '',
            'description' => '',
        ]])
        ->set('scheduledItems', [[
            'id' => null,
            'campaign_index' => 0,
            'title' => '',
            'description' => '',
            'media_type' => 'invalid-type',
            'instagram_account_id' => '',
            'scheduled_at' => '',
        ]])
        ->call('update')
        ->assertRedirect(route('proposals.index'));

    $proposal->refresh();

    expect($proposal->title)->toBe('')
        ->and($proposal->content)->toBe('')
        ->and($proposal->client_id)->toBe($client->id)
        ->and(Campaign::query()->where('proposal_id', $proposal->id)->exists())->toBeFalse()
        ->and(ScheduledPost::query()->where('client_id', $client->id)->where('user_id', $user->id)->count())->toBe(0);
});

test('next and previous step actions persist draft changes', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create([
        'title' => 'Original Title',
        'content' => '# Original',
    ]);

    $component = Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->set('title', 'Saved On Next')
        ->call('nextStep')
        ->assertSet('currentStep', 2);

    $proposal->refresh();

    expect($proposal->title)->toBe('Saved On Next');

    $component
        ->set('content', '# Saved On Previous')
        ->call('previousStep')
        ->assertSet('currentStep', 1);

    $proposal->refresh();

    expect($proposal->content)->toBe('# Saved On Previous');
});

test('step navigation autosave does not create duplicate campaigns with same name', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->set('campaigns', [[
            'id' => null,
            'name' => 'Nombre 2',
            'description' => '',
        ]])
        ->set('scheduledItems', [[
            'id' => null,
            'campaign_index' => 0,
            'title' => 'Post One',
            'description' => '',
            'media_type' => 'post',
            'instagram_account_id' => (string) $account->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]])
        ->call('nextStep')
        ->call('previousStep');

    expect(Campaign::query()
        ->where('client_id', $client->id)
        ->where('proposal_id', $proposal->id)
        ->where('name', 'Nombre 2')
        ->count())->toBe(1);
});

test('markdown preview toggle renders proposal markdown on edit page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->set('content', '# Heading')
        ->call('togglePreview')
        ->assertSet('previewMode', true)
        ->assertSeeHtml('<h1>Heading</h1>');
});

test('sent proposals render read-only state', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->sent()->create([
        'title' => 'Sent Proposal',
        'content' => '# Locked Content',
    ]);

    Livewire::actingAs($user)
        ->test(ProposalEdit::class, ['proposal' => $proposal])
        ->assertSee('This proposal is read-only');
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
