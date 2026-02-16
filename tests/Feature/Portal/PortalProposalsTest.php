<?php

use App\Enums\MediaType;
use App\Enums\ProposalStatus;
use App\Livewire\Portal\Proposals\Show as PortalProposalShow;
use App\Mail\ProposalApproved;
use App\Mail\ProposalRevisionRequested;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('portal proposals list requires authenticated client guard', function (): void {
    $this->get(route('portal.proposals.index'))
        ->assertRedirect(route('portal.login'));
});

test('portal proposals list only shows scoped non draft proposals', function (): void {
    $influencer = User::factory()->create();

    $client = Client::factory()->for($influencer)->create([
        'name' => 'Scoped Client',
    ]);

    $otherClient = Client::factory()->for($influencer)->create([
        'name' => 'Other Client',
    ]);

    $clientUser = ClientUser::factory()->for($client)->create();

    Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Draft Should Be Hidden',
        'status' => ProposalStatus::Draft,
    ]);

    $visibleProposal = Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Sent Visible Proposal',
        'status' => ProposalStatus::Sent,
        'sent_at' => now()->subDay(),
    ]);

    Proposal::factory()->for($influencer)->for($otherClient)->create([
        'title' => 'Other Client Proposal',
        'status' => ProposalStatus::Sent,
        'sent_at' => now()->subHour(),
    ]);

    $campaign = Campaign::factory()->for($client)->for($visibleProposal)->create();
    $account = InstagramAccount::factory()->for($influencer)->create();

    ScheduledPost::factory()->for($influencer)->for($client)->for($campaign)->for($account)->create([
        'title' => 'Scheduled Deliverable',
    ]);

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.proposals.index'))
        ->assertSuccessful()
        ->assertSee('Sent Visible Proposal')
        ->assertSee('href="'.route('portal.proposals.show', $visibleProposal).'"', false)
        ->assertDontSee('Draft Should Be Hidden')
        ->assertDontSee('Other Client Proposal');
});

test('portal proposals status filter is enforced in component query', function (): void {
    $influencer = User::factory()->create();
    $client = Client::factory()->for($influencer)->create();
    $clientUser = ClientUser::factory()->for($client)->create();

    Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Sent Proposal',
        'status' => ProposalStatus::Sent,
        'sent_at' => now()->subDays(3),
    ]);

    Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Approved Proposal',
        'status' => ProposalStatus::Approved,
        'sent_at' => now()->subDays(2),
    ]);

    Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Rejected Proposal',
        'status' => ProposalStatus::Rejected,
        'sent_at' => now()->subDay(),
    ]);

    Livewire::actingAs($clientUser, 'client')
        ->test(\App\Livewire\Portal\Proposals\Index::class)
        ->set('status', ProposalStatus::Approved->value)
        ->assertSee('Approved Proposal')
        ->assertDontSee('Sent Proposal')
        ->assertDontSee('Rejected Proposal');
});

test('portal proposal detail renders markdown and campaign schedule context', function (): void {
    $influencer = User::factory()->create();
    $client = Client::factory()->for($influencer)->create();
    $clientUser = ClientUser::factory()->for($client)->create();

    $proposal = Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Portal Detail Proposal',
        'content' => "# Proposal Scope\n\n- Deliverable one\n- Deliverable two",
        'status' => ProposalStatus::Sent,
        'sent_at' => now()->subDay(),
    ]);

    $campaign = Campaign::factory()->for($client)->for($proposal)->create([
        'name' => 'Spring Launch',
    ]);

    $account = InstagramAccount::factory()->for($influencer)->create([
        'username' => 'client_preview_account',
    ]);

    ScheduledPost::factory()->for($influencer)->for($client)->for($campaign)->for($account)->create([
        'title' => 'Launch Reel',
        'media_type' => MediaType::Reel,
        'scheduled_at' => now()->addDays(2)->startOfHour(),
    ]);

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('Portal Detail Proposal')
        ->assertSee('Approve')
        ->assertSee('Request Changes')
        ->assertSee('<h1>Proposal Scope</h1>', false)
        ->assertSee('<li>Deliverable one</li>', false)
        ->assertSee('Spring Launch')
        ->assertSee('Launch Reel')
        ->assertSee('Reel')
        ->assertSee('client_preview_account');
});

test('portal proposal detail cannot show draft proposals', function (): void {
    $influencer = User::factory()->create();
    $client = Client::factory()->for($influencer)->create();
    $clientUser = ClientUser::factory()->for($client)->create();

    $proposal = Proposal::factory()->for($influencer)->for($client)->create([
        'status' => ProposalStatus::Draft,
    ]);

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.proposals.show', $proposal))
        ->assertNotFound();
});

test('portal proposal detail cannot show other client proposals', function (): void {
    $influencer = User::factory()->create();
    $client = Client::factory()->for($influencer)->create();
    $otherClient = Client::factory()->for($influencer)->create();

    $clientUser = ClientUser::factory()->for($client)->create();

    $otherClientProposal = Proposal::factory()->for($influencer)->for($otherClient)->create([
        'status' => ProposalStatus::Sent,
        'sent_at' => now()->subHour(),
    ]);

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.proposals.show', $otherClientProposal))
        ->assertNotFound();
});

test('client can approve a sent proposal with campaign and scheduled content context', function (): void {
    Mail::fake();

    $influencer = User::factory()->create(['email' => 'influencer@example.test']);
    $client = Client::factory()->for($influencer)->create();
    $clientUser = ClientUser::factory()->for($client)->create();
    $account = InstagramAccount::factory()->for($influencer)->create();

    $proposal = Proposal::factory()->for($influencer)->for($client)->sent()->create([
        'title' => 'Approval Scope Proposal',
    ]);

    $campaignA = Campaign::factory()->for($client)->for($proposal)->create();
    $campaignB = Campaign::factory()->for($client)->for($proposal)->create();

    ScheduledPost::factory()->for($influencer)->for($client)->for($campaignA)->for($account)->create();
    ScheduledPost::factory()->for($influencer)->for($client)->for($campaignB)->for($account)->create();

    Livewire::actingAs($clientUser, 'client')
        ->test(PortalProposalShow::class, ['proposal' => $proposal])
        ->assertSee('Approve')
        ->assertSee('Request Changes')
        ->call('approve')
        ->assertHasNoErrors();

    $proposal->refresh();

    expect($proposal->status)->toBe(ProposalStatus::Approved)
        ->and($proposal->responded_at)->not->toBeNull();

    Mail::assertSent(ProposalApproved::class, function (ProposalApproved $mail) use ($proposal): bool {
        return $mail->hasTo('influencer@example.test')
            && $mail->proposal->is($proposal);
    });
});

test('client can request proposal changes and revision notes are required', function (): void {
    Mail::fake();

    $influencer = User::factory()->create(['email' => 'creator@example.test']);
    $client = Client::factory()->for($influencer)->create();
    $clientUser = ClientUser::factory()->for($client)->create();
    $account = InstagramAccount::factory()->for($influencer)->create();

    $proposal = Proposal::factory()->for($influencer)->for($client)->sent()->create([
        'title' => 'Needs Revision Proposal',
    ]);

    $campaign = Campaign::factory()->for($client)->for($proposal)->create();
    ScheduledPost::factory()->for($influencer)->for($client)->for($campaign)->for($account)->create();

    Livewire::actingAs($clientUser, 'client')
        ->test(PortalProposalShow::class, ['proposal' => $proposal])
        ->call('openRequestChanges')
        ->assertSet('requestingChanges', true)
        ->set('revisionNotes', 'Too short')
        ->call('requestChanges')
        ->assertHasErrors(['revisionNotes' => 'min'])
        ->set('revisionNotes', 'Please add one more story and include boosted-post usage rights.')
        ->call('requestChanges')
        ->assertHasNoErrors()
        ->assertSet('requestingChanges', false);

    $proposal->refresh();

    expect($proposal->status)->toBe(ProposalStatus::Revised)
        ->and($proposal->revision_notes)->toBe('Please add one more story and include boosted-post usage rights.')
        ->and($proposal->responded_at)->not->toBeNull();

    Mail::assertSent(ProposalRevisionRequested::class, function (ProposalRevisionRequested $mail) use ($proposal): bool {
        return $mail->hasTo('creator@example.test')
            && $mail->proposal->is($proposal);
    });
});

test('client cannot approve or request changes after proposal has already been responded to', function (): void {
    Mail::fake();

    $influencer = User::factory()->create(['email' => 'influencer@example.test']);
    $client = Client::factory()->for($influencer)->create();
    $clientUser = ClientUser::factory()->for($client)->create();

    $proposal = Proposal::factory()->for($influencer)->for($client)->approved()->create();

    Livewire::actingAs($clientUser, 'client')
        ->test(PortalProposalShow::class, ['proposal' => $proposal])
        ->assertDontSee('wire:click="approve"', false)
        ->assertDontSee('wire:click="openRequestChanges"', false)
        ->call('approve')
        ->assertHasErrors(['proposal'])
        ->call('openRequestChanges')
        ->assertHasErrors(['proposal']);

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Approved);
    Mail::assertNothingSent();
});
