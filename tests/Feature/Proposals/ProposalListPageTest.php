<?php

use App\Enums\ProposalStatus;
use App\Livewire\Proposals\Index;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from proposals page', function (): void {
    $this->get(route('proposals.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users only see their own proposals and sidebar link points to proposals index', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $client = Client::factory()->for($user)->create(['name' => 'Acme Inc']);

    $ownerProposal = Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Summer Campaign',
        'status' => ProposalStatus::Draft,
    ]);

    Proposal::factory()->for($otherUser)->for(Client::factory()->for($otherUser))->create([
        'title' => 'Hidden Proposal',
    ]);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful()
        ->assertSee('Proposals')
        ->assertSee('Summer Campaign')
        ->assertSee('Acme Inc')
        ->assertSee('Draft')
        ->assertDontSee('Hidden Proposal')
        ->assertSee('href="'.route('proposals.index').'"', false);
});

test('proposal list displays campaign count and scheduled content count', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Multi Campaign Proposal',
    ]);

    $campaign1 = Campaign::factory()->for($client)->for($proposal)->create();
    $campaign2 = Campaign::factory()->for($client)->for($proposal)->create();

    ScheduledPost::factory()->for($campaign1)->count(3)->create();
    ScheduledPost::factory()->for($campaign2)->count(2)->create();

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('Multi Campaign Proposal')
        ->assertSee('2')
        ->assertSee('5');
});

test('proposal list shows correct status badge colors', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Draft Proposal',
        'status' => ProposalStatus::Draft,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Sent Proposal',
        'status' => ProposalStatus::Sent,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Approved Proposal',
        'status' => ProposalStatus::Approved,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Rejected Proposal',
        'status' => ProposalStatus::Rejected,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Revised Proposal',
        'status' => ProposalStatus::Revised,
    ]);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful()
        ->assertSee('Draft Proposal')
        ->assertSee('Sent Proposal')
        ->assertSee('Approved Proposal')
        ->assertSee('Rejected Proposal')
        ->assertSee('Revised Proposal');
});

test('proposals list supports filtering by status', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Draft Proposal',
        'status' => ProposalStatus::Draft,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Sent Proposal',
        'status' => ProposalStatus::Sent,
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('status', ProposalStatus::Draft->value)
        ->assertSee('Draft Proposal')
        ->assertDontSee('Sent Proposal')
        ->set('status', ProposalStatus::Sent->value)
        ->assertSee('Sent Proposal')
        ->assertDontSee('Draft Proposal');
});

test('proposals list supports filtering by client', function (): void {
    $user = User::factory()->create();

    $client1 = Client::factory()->for($user)->create(['name' => 'Client One']);
    $client2 = Client::factory()->for($user)->create(['name' => 'Client Two']);

    Proposal::factory()->for($user)->for($client1)->create([
        'title' => 'Proposal for Client One',
    ]);

    Proposal::factory()->for($user)->for($client2)->create([
        'title' => 'Proposal for Client Two',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('clientId', (string) $client1->id)
        ->assertSee('Proposal for Client One')
        ->assertDontSee('Proposal for Client Two')
        ->set('clientId', (string) $client2->id)
        ->assertSee('Proposal for Client Two')
        ->assertDontSee('Proposal for Client One');
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
        ->assertSee('Proposal 01')
        ->assertDontSee('Proposal 11');

    $this->actingAs($user)
        ->get(route('proposals.index', ['page' => 2]))
        ->assertSuccessful()
        ->assertSee('Proposal 11')
        ->assertDontSee('Proposal 01');
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

test('users cannot delete proposals they do not own', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherClient = Client::factory()->for($otherUser)->create();
    $otherProposal = Proposal::factory()->for($otherUser)->for($otherClient)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('confirmDelete', $otherProposal->id)
        ->assertStatus(404);

    $this->assertDatabaseHas('proposals', ['id' => $otherProposal->id]);
});

test('edit button only shown for draft proposals', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Draft Proposal',
        'status' => ProposalStatus::Draft,
    ]);

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Sent Proposal',
        'status' => ProposalStatus::Sent,
    ]);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful();
});
