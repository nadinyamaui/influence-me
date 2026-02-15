<?php

use App\Enums\ProposalStatus;
use App\Livewire\Proposals\Index;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from proposals page', function (): void {
    $this->get(route('proposals.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users only see their own proposals and proposals sidebar link points to proposals index', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $client = Client::factory()->for($user)->create(['name' => 'Owner Client']);
    $proposal = Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Visible Proposal',
        'status' => ProposalStatus::Draft,
    ]);

    $otherClient = Client::factory()->for($otherUser)->create(['name' => 'Hidden Client']);
    Proposal::factory()->for($otherUser)->for($otherClient)->create([
        'title' => 'Hidden Proposal',
    ]);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful()
        ->assertSee('Proposals')
        ->assertSee('Visible Proposal')
        ->assertSee('Owner Client')
        ->assertSee('Draft')
        ->assertDontSee('Hidden Proposal')
        ->assertSee('href="'.route('proposals.index').'"', false)
        ->assertSee('href="'.url('/proposals/'.$proposal->id).'"', false);
});

test('proposals list supports filtering by status and renders status badge color classes', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->create(['title' => 'Draft Proposal', 'status' => ProposalStatus::Draft]);
    Proposal::factory()->for($user)->for($client)->create(['title' => 'Sent Proposal', 'status' => ProposalStatus::Sent]);
    Proposal::factory()->for($user)->for($client)->create(['title' => 'Approved Proposal', 'status' => ProposalStatus::Approved]);
    Proposal::factory()->for($user)->for($client)->create(['title' => 'Rejected Proposal', 'status' => ProposalStatus::Rejected]);
    Proposal::factory()->for($user)->for($client)->create(['title' => 'Revised Proposal', 'status' => ProposalStatus::Revised]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('status', ProposalStatus::Draft->value)
        ->assertSee('Draft Proposal')
        ->assertDontSee('Sent Proposal')
        ->set('status', ProposalStatus::Sent->value)
        ->assertSee('Sent Proposal')
        ->assertDontSee('Draft Proposal')
        ->set('status', ProposalStatus::Approved->value)
        ->assertSee('Approved Proposal')
        ->assertDontSee('Rejected Proposal')
        ->set('status', ProposalStatus::Rejected->value)
        ->assertSee('Rejected Proposal')
        ->assertDontSee('Revised Proposal')
        ->set('status', ProposalStatus::Revised->value)
        ->assertSee('Revised Proposal')
        ->assertDontSee('Approved Proposal');

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('bg-zinc-100 text-zinc-700', false)
        ->assertSee('bg-sky-100 text-sky-700', false)
        ->assertSee('bg-emerald-100 text-emerald-700', false)
        ->assertSee('bg-rose-100 text-rose-700', false)
        ->assertSee('bg-amber-100 text-amber-700', false);
});

test('proposals list supports filtering by client', function (): void {
    $user = User::factory()->create();
    $firstClient = Client::factory()->for($user)->create(['name' => 'First Client']);
    $secondClient = Client::factory()->for($user)->create(['name' => 'Second Client']);

    Proposal::factory()->for($user)->for($firstClient)->create(['title' => 'First Client Proposal']);
    Proposal::factory()->for($user)->for($secondClient)->create(['title' => 'Second Client Proposal']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('clientId', (string) $firstClient->id)
        ->assertSee('First Client Proposal')
        ->assertDontSee('Second Client Proposal')
        ->set('clientId', (string) $secondClient->id)
        ->assertSee('Second Client Proposal')
        ->assertDontSee('First Client Proposal');
});

test('proposals list shows campaign and scheduled content counts per proposal', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = InstagramAccount::factory()->for($user)->create();

    $proposalWithCounts = Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Counted Proposal',
    ]);
    $proposalWithoutCounts = Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Zero Proposal',
    ]);

    $firstCampaign = Campaign::factory()->for($client)->create([
        'proposal_id' => $proposalWithCounts->id,
    ]);
    $secondCampaign = Campaign::factory()->for($client)->create([
        'proposal_id' => $proposalWithCounts->id,
    ]);

    Campaign::factory()->for($client)->create([
        'proposal_id' => $proposalWithoutCounts->id,
    ]);

    ScheduledPost::factory()->for($user)->for($client)->for($account)->create([
        'campaign_id' => $firstCampaign->id,
    ]);
    ScheduledPost::factory()->for($user)->for($client)->for($account)->create([
        'campaign_id' => $firstCampaign->id,
    ]);
    ScheduledPost::factory()->for($user)->for($client)->for($account)->create([
        'campaign_id' => $secondCampaign->id,
    ]);

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('Counted Proposal')
        ->assertSee('Zero Proposal')
        ->assertSee('2')
        ->assertSee('3')
        ->assertSee('0');
});

test('proposals list paginates results', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    foreach (range(1, 11) as $number) {
        Proposal::factory()->for($user)->for($client)->create([
            'title' => 'Proposal '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
        ]);
    }

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('Proposal 11')
        ->assertDontSee('Proposal 01');

    $this->actingAs($user)
        ->get(route('proposals.index', ['page' => 2]))
        ->assertSuccessful()
        ->assertSee('Proposal 01')
        ->assertDontSee('Proposal 11');
});

test('proposals list shows empty state when user has no proposals', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('No proposals yet. Create your first proposal to send to a client.');
});

test('owners can delete proposals from the list page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('confirmDelete', $proposal->id)
        ->assertSet('deletingProposalId', $proposal->id)
        ->call('delete')
        ->assertSet('deletingProposalId', null);

    $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
});
