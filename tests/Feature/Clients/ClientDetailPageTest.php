<?php

use App\Enums\ClientType;
use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Livewire\Clients\Show;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\SocialAccountMedia;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\SocialAccount;
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

    $account = SocialAccount::factory()->for($owner)->create();
    $media = SocialAccountMedia::factory()->for($account)->create();
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
        ->assertSee('Campaigns')
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
        ->set('activeTab', 'campaigns')
        ->assertSee('Campaigns')
        ->assertSee('Add Campaign')
        ->set('activeTab', 'proposals')
        ->assertSee('Proposals tab coming soon.')
        ->set('activeTab', 'invoices')
        ->assertSee('Invoices tab coming soon.')
        ->set('activeTab', 'analytics')
        ->assertSee('Total Linked Posts')
        ->assertSee('Performance Over Time')
        ->assertSee('Campaign Breakdown')
        ->assertSee('Comparison to Account Average');
});

test('non-owners cannot view client detail page', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $client = Client::factory()->for($owner)->create();

    $this->actingAs($outsider)
        ->get(route('clients.show', $client))
        ->assertForbidden();
});

test('content tab groups linked media by campaign entity and shows aggregate stats', function (): void {
    $owner = User::factory()->create();

    $client = Client::factory()->for($owner)->create();
    $account = SocialAccount::factory()->for($owner)->create();

    $launchFirst = SocialAccountMedia::factory()->for($account)->create([
        'caption' => 'Launch Post One',
        'reach' => 1000,
        'impressions' => 2100,
        'engagement_rate' => 5.5,
    ]);

    $launchSecond = SocialAccountMedia::factory()->for($account)->create([
        'caption' => 'Launch Post Two',
        'reach' => 500,
        'impressions' => 900,
        'engagement_rate' => 4.5,
    ]);

    $uncategorized = SocialAccountMedia::factory()->for($account)->create([
        'caption' => 'No Campaign Post',
        'reach' => 300,
        'impressions' => 500,
        'engagement_rate' => 2.0,
    ]);

    $launchCampaign = Campaign::factory()->for($client)->create(['name' => 'Launch Campaign']);
    $secondCampaign = Campaign::factory()->for($client)->create(['name' => 'Retention Campaign']);

    $launchCampaign->instagramMedia()->attach($launchFirst->id);
    $launchCampaign->instagramMedia()->attach($launchSecond->id);
    $secondCampaign->instagramMedia()->attach($uncategorized->id);

    $component = Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'content')
        ->assertSee('Launch Campaign')
        ->assertSee('Retention Campaign')
        ->assertSee('1,800')
        ->assertSee('3,500')
        ->assertSee('4.00%')
        ->assertSee('Launch Post One')
        ->assertSee('Launch Post Two')
        ->assertSee('No Campaign Post');

    $campaignIds = collect($component->get('linkedContentGroups'))
        ->pluck('campaign_id')
        ->filter()
        ->all();

    expect($campaignIds)->toContain($launchCampaign->id)
        ->and($campaignIds)->toContain($secondCampaign->id);
});

test('analytics tab shows aggregate metrics, campaign breakdown, trend, and account comparison', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $otherClient = Client::factory()->for($owner)->create();
    $account = SocialAccount::factory()->for($owner)->create();

    $campaignA = Campaign::factory()->for($client)->create(['name' => 'Summer Launch']);
    $campaignB = Campaign::factory()->for($client)->create(['name' => 'Product Review']);

    $mediaOne = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(4),
        'reach' => 1000,
        'impressions' => 2000,
        'engagement_rate' => 4.0,
    ]);
    $mediaTwo = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(2),
        'reach' => 1200,
        'impressions' => 1500,
        'engagement_rate' => 5.0,
    ]);
    $mediaThree = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(1),
        'reach' => 300,
        'impressions' => 400,
        'engagement_rate' => 2.0,
    ]);
    $unlinkedMedia = SocialAccountMedia::factory()->for($account)->create([
        'published_at' => now()->subDays(6),
        'reach' => 800,
        'impressions' => 1100,
        'engagement_rate' => 10.0,
    ]);

    $campaignA->instagramMedia()->attach($mediaOne->id);
    $campaignA->instagramMedia()->attach($mediaTwo->id);
    $campaignB->instagramMedia()->attach($mediaThree->id);

    $otherCampaign = Campaign::factory()->for($otherClient)->create(['name' => 'Other Campaign']);
    $otherCampaign->instagramMedia()->attach($unlinkedMedia->id);

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'analytics')
        ->assertSee('Total Linked Posts')
        ->assertSee('3')
        ->assertSee('2,500')
        ->assertSee('3,900')
        ->assertSee('3.67%')
        ->assertSee('Performance Over Time')
        ->assertSee('Campaign Breakdown')
        ->assertSee('Summer Launch')
        ->assertSee('Product Review')
        ->assertSee('2')
        ->assertSee('1')
        ->assertSee('2,200')
        ->assertSee('300')
        ->assertSee('4.50%')
        ->assertSee('2.00%')
        ->assertSee('Comparison to Account Average')
        ->assertSee('Client Linked Content')
        ->assertSee('Overall Account Average')
        ->assertSee('5.25%')
        ->assertViewHas('clientAnalytics.summary.total_linked_posts', 3)
        ->assertViewHas('clientAnalytics.summary.total_reach', 2500)
        ->assertViewHas('clientAnalytics.summary.total_impressions', 3900)
        ->assertViewHas('clientAnalytics.summary.average_engagement_rate', 3.67)
        ->assertViewHas('clientAnalytics.trend.values', [4.0, 5.0, 2.0])
        ->assertViewHas('clientAnalytics.comparison.client_average_engagement_rate', 3.67)
        ->assertViewHas('clientAnalytics.comparison.account_average_engagement_rate', 5.25);
});

test('analytics tab shows empty state when no linked content exists', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'analytics')
        ->assertSee('No linked content available yet. Link campaign content to unlock client analytics.')
        ->assertDontSee('Campaign Breakdown')
        ->assertViewHas('clientAnalytics.has_linked_content', false)
        ->assertViewHas('clientAnalytics.summary.total_linked_posts', 0);
});

test('owners can unlink linked media from client content tab', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $account = SocialAccount::factory()->for($owner)->create();
    $media = SocialAccountMedia::factory()->for($account)->create();

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

test('campaigns tab shows empty state then supports campaign create edit unlink proposal and delete', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $otherClient = Client::factory()->for($owner)->create();

    $proposal = Proposal::factory()->for($owner)->for($client)->create([
        'title' => 'Client Proposal',
    ]);

    Proposal::factory()->for($owner)->for($otherClient)->create([
        'title' => 'Other Client Proposal',
    ]);

    $component = Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'campaigns')
        ->assertSee('Add Campaign')
        ->call('openCreateCampaignModal')
        ->set('campaignForm.name', 'Spring Launch')
        ->set('campaignForm.description', 'Seasonal campaign rollout')
        ->set('campaignForm.proposalId', (string) $proposal->id)
        ->call('saveCampaign')
        ->assertSee('Spring Launch')
        ->assertSee('Client Proposal');

    $campaign = Campaign::query()->where('client_id', $client->id)->where('name', 'Spring Launch')->first();

    expect($campaign)->not->toBeNull();

    $component->call('openEditCampaignModal', $campaign->id)
        ->set('campaignForm.name', 'Spring Launch Updated')
        ->set('campaignForm.description', '')
        ->set('campaignForm.proposalId', '')
        ->call('saveCampaign')
        ->assertSee('Spring Launch Updated')
        ->assertSee('Proposal: Not linked')
        ->call('deleteCampaign', $campaign->id);

    $this->assertDatabaseMissing('campaigns', [
        'id' => $campaign->id,
    ]);
});

test('campaign create enforces client scoped proposal validation', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $otherClient = Client::factory()->for($owner)->create();

    $otherClientProposal = Proposal::factory()->for($owner)->for($otherClient)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'campaigns')
        ->call('openCreateCampaignModal')
        ->set('campaignForm.name', 'Invalid Proposal Link')
        ->set('campaignForm.proposalId', (string) $otherClientProposal->id)
        ->call('saveCampaign')
        ->assertHasErrors(['campaignForm.proposalId']);
});

test('campaigns tab shows empty state when client has no campaigns', function (): void {
    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['client' => $client])
        ->set('activeTab', 'campaigns')
        ->assertSee('No campaigns yet. Add a campaign to organize this client');
});
