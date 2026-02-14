<?php

use App\Enums\ClientType;
use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Livewire\Clients\Show;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from the client detail page', function (): void {
    $client = Client::factory()->create();

    $this->get(route('clients.show', $client))
        ->assertRedirect(route('login'));
});

test('owner can view client detail page with summary and tabs', function (): void {
    $owner = User::factory()->create();

    $client = Client::factory()->for($owner)->create([
        'name' => 'Acme Client',
        'company_name' => 'Acme Company',
        'type' => ClientType::Brand,
        'email' => 'client@example.test',
        'phone' => '555-222-3333',
        'notes' => 'Priority client',
    ]);

    ClientUser::factory()->for($client)->create();

    $account = InstagramAccount::factory()->for($owner)->create();
    $media = InstagramMedia::factory()->for($account)->create();
    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'Uncategorized',
    ]);
    $campaign->instagramMedia()->attach($media->id);

    Proposal::factory()->for($owner)->for($client)->create([
        'status' => ProposalStatus::Sent,
    ]);

    Invoice::factory()->for($owner)->for($client)->create([
        'status' => InvoiceStatus::Sent,
        'total' => 1234.56,
    ]);

    $this->actingAs($owner)
        ->get(route('clients.show', $client))
        ->assertSuccessful()
        ->assertSee('Acme Client')
        ->assertSee('Acme Company')
        ->assertSee('client@example.test')
        ->assertSee('555-222-3333')
        ->assertSee('Priority client')
        ->assertSee('Portal access: Active')
        ->assertSee('Overview')
        ->assertSee('Content')
        ->assertSee('Proposals')
        ->assertSee('Invoices')
        ->assertSee('Analytics')
        ->assertSee('Total linked posts')
        ->assertSee('Active proposals')
        ->assertSee('Pending invoices')
        ->assertSee('$1,234.56')
        ->assertSee('href="'.route('clients.edit', $client).'"', false);

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'content')
        ->assertSee('Total reach')
        ->assertSee('Total impressions')
        ->assertSee('Average engagement rate')
        ->assertSee('Uncategorized')
        ->assertSee('Unlink')
        ->set('activeTab', 'proposals')
        ->assertSee('Proposals tab coming soon.')
        ->set('activeTab', 'invoices')
        ->assertSee('Invoices tab coming soon.')
        ->set('activeTab', 'analytics')
        ->assertSee('Analytics tab coming soon.');
});

test('non-owners cannot view client detail page', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $client = Client::factory()->for($owner)->create();

    $this->actingAs($outsider)
        ->get(route('clients.show', $client))
        ->assertForbidden();
});

test('content tab groups linked media by campaign and shows aggregate stats', function (): void {
    $owner = User::factory()->create();

    $client = Client::factory()->for($owner)->create();
    $account = InstagramAccount::factory()->for($owner)->create();

    $launchFirst = InstagramMedia::factory()->for($account)->create([
        'caption' => 'Launch Post One',
        'reach' => 1000,
        'impressions' => 2100,
        'engagement_rate' => 5.5,
    ]);

    $launchSecond = InstagramMedia::factory()->for($account)->create([
        'caption' => 'Launch Post Two',
        'reach' => 500,
        'impressions' => 900,
        'engagement_rate' => 4.5,
    ]);

    $uncategorized = InstagramMedia::factory()->for($account)->create([
        'caption' => 'No Campaign Post',
        'reach' => 300,
        'impressions' => 500,
        'engagement_rate' => 2.0,
    ]);

    $launchCampaign = Campaign::factory()->for($client)->create([
        'name' => 'Launch Campaign',
    ]);
    $uncategorizedCampaign = Campaign::factory()->for($client)->create([
        'name' => 'Uncategorized',
    ]);

    $launchCampaign->instagramMedia()->attach($launchFirst->id);
    $launchCampaign->instagramMedia()->attach($launchSecond->id);
    $uncategorizedCampaign->instagramMedia()->attach($uncategorized->id);

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'content')
        ->assertSee('Launch Campaign')
        ->assertSee('Uncategorized')
        ->assertSee('1,800')
        ->assertSee('3,500')
        ->assertSee('4.00%')
        ->assertSee('Launch Post One')
        ->assertSee('Launch Post Two')
        ->assertSee('No Campaign Post');
});

test('owners can unlink linked media from client content tab', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $account = InstagramAccount::factory()->for($owner)->create();
    $media = InstagramMedia::factory()->for($account)->create();

    $campaign = Campaign::factory()->for($client)->create([
        'name' => 'To Remove',
    ]);
    $campaign->instagramMedia()->attach($media->id);

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'content')
        ->call('unlinkContent', $media->id)
        ->assertSee('No content linked to this client yet. Go to the Content browser to link posts.');

    $this->assertDatabaseMissing('campaign_media', [
        'campaign_id' => $campaign->id,
        'instagram_media_id' => $media->id,
    ]);
});
