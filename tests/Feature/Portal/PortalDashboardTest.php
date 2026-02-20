<?php

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\SocialAccountMedia;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\SocialAccount;
use App\Models\User;

test('portal dashboard displays scoped summary metrics and recent activity', function (): void {
    $influencer = User::factory()->create(['name' => 'Nadin Creator']);

    $client = Client::factory()->for($influencer)->create([
        'name' => 'Avery Client',
    ]);

    $otherClient = Client::factory()->for($influencer)->create([
        'name' => 'Hidden Client',
    ]);

    $clientUser = ClientUser::factory()->for($client)->create();

    $visibleProposal = Proposal::factory()->for($influencer)->for($client)->create([
        'title' => 'Scoped Proposal',
        'status' => ProposalStatus::Sent,
    ]);

    Proposal::factory()->for($influencer)->for($otherClient)->create([
        'title' => 'Hidden Proposal',
        'status' => ProposalStatus::Sent,
    ]);

    $visibleSentInvoice = Invoice::factory()->for($influencer)->for($client)->create([
        'status' => InvoiceStatus::Sent,
        'invoice_number' => '1001',
        'total' => 500,
    ]);

    Invoice::factory()->for($influencer)->for($client)->create([
        'status' => InvoiceStatus::Overdue,
        'invoice_number' => '1002',
        'total' => 250,
    ]);

    Invoice::factory()->for($influencer)->for($client)->create([
        'status' => InvoiceStatus::Paid,
        'invoice_number' => '1003',
        'total' => 100,
    ]);

    Invoice::factory()->for($influencer)->for($otherClient)->create([
        'status' => InvoiceStatus::Sent,
        'invoice_number' => '9999',
        'total' => 9000,
    ]);

    $account = SocialAccount::factory()->for($influencer)->create();

    $firstVisibleMedia = SocialAccountMedia::factory()->for($account)->create([
        'reach' => 1500,
    ]);

    $secondVisibleMedia = SocialAccountMedia::factory()->for($account)->create([
        'reach' => 500,
    ]);

    $hiddenMedia = SocialAccountMedia::factory()->for($account)->create([
        'reach' => 9000,
    ]);

    $clientCampaign = Campaign::factory()->for($client)->create();
    $otherClientCampaign = Campaign::factory()->for($otherClient)->create();
    $clientCampaign->instagramMedia()->attach([$firstVisibleMedia->id, $secondVisibleMedia->id]);
    $otherClientCampaign->instagramMedia()->attach([$hiddenMedia->id]);

    $response = $this->actingAs($clientUser, 'client')
        ->get(route('portal.dashboard'));

    $response->assertSuccessful()
        ->assertSee('Welcome back, Avery Client')
        ->assertSee('You are collaborating with Nadin Creator.')
        ->assertSee('Active Proposals')
        ->assertSee('Pending Invoices')
        ->assertSee('Linked Content')
        ->assertSee('Total Reach')
        ->assertSee('Scoped Proposal')
        ->assertSee('Invoice #1001')
        ->assertSee('$750.00')
        ->assertSee('2')
        ->assertSee('2,000')
        ->assertSee('href="'.url('/portal/proposals/'.$visibleProposal->id).'"', false)
        ->assertSee('href="'.url('/portal/invoices/'.$visibleSentInvoice->id).'"', false)
        ->assertDontSee('Hidden Proposal')
        ->assertDontSee('Invoice #9999')
        ->assertDontSee('Hidden Client');
});

test('portal dashboard shows empty activity states for clients without data', function (): void {
    $influencer = User::factory()->create();
    $client = Client::factory()->for($influencer)->create([
        'name' => 'No Data Client',
    ]);

    $clientUser = ClientUser::factory()->for($client)->create();

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.dashboard'))
        ->assertSuccessful()
        ->assertSee('Welcome back, No Data Client')
        ->assertSee('$0.00')
        ->assertSee('No proposals yet.')
        ->assertSee('No invoices yet.');
});
