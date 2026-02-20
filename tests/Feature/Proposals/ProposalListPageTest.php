<?php

use App\Enums\ProposalStatus;
use App\Livewire\Proposals\Index;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\SocialAccount;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from proposals page', function (): void {
    $this->get(route('proposals.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users only see their own proposals', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $client = Client::factory()->for($user)->create(['name' => 'Acme Corp']);

    Proposal::factory()
        ->for($user)
        ->for($client)
        ->create(['title' => 'My Proposal']);

    $otherClient = Client::factory()->for($otherUser)->create();
    Proposal::factory()
        ->for($otherUser)
        ->for($otherClient)
        ->create(['title' => 'Hidden Proposal']);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful()
        ->assertSee('Proposals')
        ->assertSee('My Proposal')
        ->assertSee('Acme Corp')
        ->assertDontSee('Hidden Proposal');
});

test('proposals list shows campaign count and scheduled content count', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $account = SocialAccount::factory()->for($user)->create();

    $proposal = Proposal::factory()
        ->for($user)
        ->for($client)
        ->create(['title' => 'Campaign Proposal']);

    $campaign1 = Campaign::factory()->for($client)->create(['proposal_id' => $proposal->id]);
    $campaign2 = Campaign::factory()->for($client)->create(['proposal_id' => $proposal->id]);

    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaign1->id,
        'social_account_id' => $account->id,
    ]);
    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaign1->id,
        'social_account_id' => $account->id,
    ]);
    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaign2->id,
        'social_account_id' => $account->id,
    ]);

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('Campaign Proposal')
        ->assertSeeInOrder(['2', '3']);
});

test('proposals list filters by status', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->draft()->create(['title' => 'Draft Proposal']);
    Proposal::factory()->for($user)->for($client)->sent()->create(['title' => 'Sent Proposal']);
    Proposal::factory()->for($user)->for($client)->approved()->create(['title' => 'Approved Proposal']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('status', ProposalStatus::Draft->value)
        ->assertSee('Draft Proposal')
        ->assertDontSee('Sent Proposal')
        ->assertDontSee('Approved Proposal')
        ->set('status', ProposalStatus::Sent->value)
        ->assertSee('Sent Proposal')
        ->assertDontSee('Draft Proposal')
        ->assertDontSee('Approved Proposal')
        ->set('status', ProposalStatus::Approved->value)
        ->assertSee('Approved Proposal')
        ->assertDontSee('Draft Proposal')
        ->assertDontSee('Sent Proposal');
});

test('proposals list filters by client', function (): void {
    $user = User::factory()->create();

    $clientA = Client::factory()->for($user)->create(['name' => 'Client Alpha']);
    $clientB = Client::factory()->for($user)->create(['name' => 'Client Beta']);

    Proposal::factory()->for($user)->for($clientA)->create(['title' => 'Alpha Proposal']);
    Proposal::factory()->for($user)->for($clientB)->create(['title' => 'Beta Proposal']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('client', (string) $clientA->id)
        ->assertSee('Alpha Proposal')
        ->assertDontSee('Beta Proposal')
        ->set('client', (string) $clientB->id)
        ->assertSee('Beta Proposal')
        ->assertDontSee('Alpha Proposal')
        ->set('client', 'all')
        ->assertSee('Alpha Proposal')
        ->assertSee('Beta Proposal');
});

test('proposals list shows correct status badge colors', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->draft()->create(['title' => 'Draft One']);
    Proposal::factory()->for($user)->for($client)->sent()->create(['title' => 'Sent One']);
    Proposal::factory()->for($user)->for($client)->approved()->create(['title' => 'Approved One']);
    Proposal::factory()->for($user)->for($client)->rejected()->create(['title' => 'Rejected One']);
    Proposal::factory()->for($user)->for($client)->revised()->create(['title' => 'Revised One']);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful()
        ->assertSee('bg-zinc-100')
        ->assertSee('bg-blue-100')
        ->assertSee('bg-emerald-100')
        ->assertSee('bg-rose-100')
        ->assertSee('bg-amber-100');
});

test('proposals list paginates results', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    foreach (range(1, 11) as $number) {
        Proposal::factory()->for($user)->for($client)->create([
            'title' => 'Proposal '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'created_at' => now()->subMinutes($number),
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

test('edit button shows for draft and revised proposals only', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->draft()->create(['title' => 'Draft One']);
    Proposal::factory()->for($user)->for($client)->revised()->create(['title' => 'Revised One']);
    Proposal::factory()->for($user)->for($client)->sent()->create(['title' => 'Sent One']);

    $response = $this->actingAs($user)->get(route('proposals.index'));

    $response->assertSuccessful();

    $content = $response->getContent();
    expect(substr_count($content, 'fa-pen-to-square'))->toBe(4);
});

test('owners can delete proposals from the list page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $proposal->id);

    $this->assertDatabaseMissing('proposals', ['id' => $proposal->id]);
});

test('sidebar link points to proposals index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('href="'.route('proposals.index').'"', false);
});

test('proposal title links to proposal preview page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $proposal = Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Linked Preview Proposal',
    ]);

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('href="'.route('proposals.show', $proposal).'"', false)
        ->assertSee('Linked Preview Proposal');
});

test('proposals list includes mobile card layout and desktop table layout', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Proposal::factory()->for($user)->for($client)->create([
        'title' => 'Responsive Proposal',
    ]);

    $this->actingAs($user)
        ->get(route('proposals.index'))
        ->assertSuccessful()
        ->assertSee('proposal-card-', false)
        ->assertSee('class="hidden overflow-x-auto sm:block"', false);
});
