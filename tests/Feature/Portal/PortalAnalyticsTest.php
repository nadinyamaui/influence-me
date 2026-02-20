<?php

use App\Enums\DemographicType;
use App\Models\AudienceDemographic;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\InstagramMedia;
use App\Models\SocialAccount;
use App\Models\User;

test('portal analytics requires authenticated client guard', function (): void {
    $this->get(route('portal.analytics.index'))
        ->assertRedirect(route('portal.login'));
});

test('portal analytics shows only client scoped campaign and demographic data', function (): void {
    $influencer = User::factory()->create();

    $client = Client::factory()->for($influencer)->create([
        'name' => 'Scoped Client',
    ]);

    $otherClient = Client::factory()->for($influencer)->create([
        'name' => 'Hidden Client',
    ]);

    $clientUser = ClientUser::factory()->for($client)->create();

    $visibleAccount = SocialAccount::factory()->for($influencer)->create([
        'username' => 'scoped_account',
        'followers_count' => 1000,
    ]);

    $hiddenAccount = SocialAccount::factory()->for($influencer)->create([
        'username' => 'hidden_account',
        'followers_count' => 1200,
    ]);

    $visibleCampaign = Campaign::factory()->for($client)->create([
        'name' => 'Scoped Campaign',
    ]);

    $hiddenCampaign = Campaign::factory()->for($otherClient)->create([
        'name' => 'Hidden Campaign',
    ]);

    $firstVisibleMedia = InstagramMedia::factory()->for($visibleAccount)->create([
        'published_at' => now()->subDays(3),
        'reach' => 1400,
        'impressions' => 2100,
        'engagement_rate' => 4.5,
    ]);

    $secondVisibleMedia = InstagramMedia::factory()->for($visibleAccount)->create([
        'published_at' => now()->subDays(1),
        'reach' => 900,
        'impressions' => 1700,
        'engagement_rate' => 5.5,
    ]);

    $hiddenMedia = InstagramMedia::factory()->for($hiddenAccount)->create([
        'published_at' => now()->subDays(2),
        'reach' => 9900,
        'impressions' => 19900,
        'engagement_rate' => 9.9,
    ]);

    $visibleCampaign->instagramMedia()->attach([$firstVisibleMedia->id, $secondVisibleMedia->id]);
    $hiddenCampaign->instagramMedia()->attach([$hiddenMedia->id]);

    AudienceDemographic::factory()->for($visibleAccount)->create([
        'type' => DemographicType::Age,
        'dimension' => '25-34',
        'value' => 60,
    ]);

    AudienceDemographic::factory()->for($visibleAccount)->create([
        'type' => DemographicType::Gender,
        'dimension' => 'Female',
        'value' => 55,
    ]);

    AudienceDemographic::factory()->for($visibleAccount)->create([
        'type' => DemographicType::City,
        'dimension' => 'New York',
        'value' => 22,
    ]);

    AudienceDemographic::factory()->for($visibleAccount)->create([
        'type' => DemographicType::Country,
        'dimension' => 'United States',
        'value' => 70,
    ]);

    AudienceDemographic::factory()->for($hiddenAccount)->create([
        'type' => DemographicType::City,
        'dimension' => 'Hidden City',
        'value' => 99,
    ]);

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.analytics.index'))
        ->assertSuccessful()
        ->assertSee('Campaign performance and audience insights for content linked to Scoped Client.')
        ->assertSee('2,300')
        ->assertSee('3,800')
        ->assertSee('5.00%')
        ->assertSee('Scoped Campaign')
        ->assertSee('New York')
        ->assertSee('United States')
        ->assertDontSee('Hidden Campaign')
        ->assertDontSee('Hidden City');
});

test('portal analytics shows empty state when client has no linked content', function (): void {
    $influencer = User::factory()->create();
    $client = Client::factory()->for($influencer)->create([
        'name' => 'No Link Client',
    ]);

    $clientUser = ClientUser::factory()->for($client)->create();

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.analytics.index'))
        ->assertSuccessful()
        ->assertSee('No linked content available yet. Linked campaign content is required before analytics can be displayed.');
});
