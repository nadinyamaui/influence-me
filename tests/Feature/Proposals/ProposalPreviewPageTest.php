<?php

use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;

test('guests are redirected from proposal preview page', function (): void {
    $proposal = Proposal::factory()->create();

    $this->get(route('proposals.show', $proposal))
        ->assertRedirect(route('login'));
});

test('proposal preview enforces ownership authorization', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    $proposal = Proposal::factory()->for($owner)->for($client)->create();

    $this->actingAs($otherUser)
        ->get(route('proposals.show', $proposal))
        ->assertForbidden();
});

test('owner can view proposal markdown campaign totals and sorted scheduled content', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create(['name' => 'Acme Corp']);
    $account = InstagramAccount::factory()->for($user)->create(['username' => 'influence_main']);

    $proposal = Proposal::factory()
        ->for($user)
        ->for($client)
        ->draft()
        ->create([
            'title' => 'Spring Launch Proposal',
            'content' => "# Campaign Overview\n\nThis is **important** markdown.",
        ]);

    $campaignA = Campaign::factory()->for($client)->create([
        'proposal_id' => $proposal->id,
        'name' => 'Spring Drop',
        'description' => 'Launch week content plan',
    ]);

    $campaignB = Campaign::factory()->for($client)->create([
        'proposal_id' => $proposal->id,
        'name' => 'Retargeting',
        'description' => null,
    ]);

    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaignA->id,
        'instagram_account_id' => $account->id,
        'title' => 'Late Reel',
        'media_type' => 'reel',
        'scheduled_at' => now()->addDays(3),
    ]);

    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaignA->id,
        'instagram_account_id' => $account->id,
        'title' => 'Early Post',
        'media_type' => 'post',
        'scheduled_at' => now()->addDay(),
    ]);

    ScheduledPost::factory()->for($user)->for($client)->create([
        'campaign_id' => $campaignB->id,
        'instagram_account_id' => $account->id,
        'title' => 'Story Reminder',
        'media_type' => 'story',
        'scheduled_at' => now()->addDays(2),
    ]);

    $response = $this->actingAs($user)->get(route('proposals.show', $proposal));

    $response->assertSuccessful()
        ->assertSee('Spring Launch Proposal')
        ->assertSee('Acme Corp')
        ->assertSee('href="'.route('clients.show', $client).'"', false)
        ->assertSee('Campaign Plan')
        ->assertSee('2 campaigns')
        ->assertSee('3 scheduled items')
        ->assertSee('Spring Drop')
        ->assertSee('Retargeting')
        ->assertSee('influence_main')
        ->assertSee('Post')
        ->assertSee('Reel')
        ->assertSee('Story')
        ->assertSee('Campaign Overview')
        ->assertSee('important', false)
        ->assertSeeInOrder(['Early Post', 'Late Reel']);
});

test('proposal preview strips raw html from markdown content', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()
        ->for($user)
        ->for($client)
        ->create([
            'content' => "# Heading\n\n<script>alert('xss')</script>\n\n**Safe text**",
        ]);

    $this->actingAs($user)
        ->get(route('proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('<h1>Heading</h1>', false)
        ->assertDontSee("<script>alert('xss')</script>", false)
        ->assertSee('Safe text', false);
});

test('draft proposal preview shows edit and send to client actions', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->draft()->create();

    $this->actingAs($user)
        ->get(route('proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('Edit')
        ->assertSee('Send to Client');
});

test('sent proposal preview shows waiting state', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->sent()->create();

    $this->actingAs($user)
        ->get(route('proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('Waiting for response...');
});

test('approved proposal preview shows approved badge', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->approved()->create();

    $this->actingAs($user)
        ->get(route('proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('Approved');
});

test('rejected proposal preview shows rejected badge', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->rejected()->create();

    $this->actingAs($user)
        ->get(route('proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('Rejected');
});

test('revised proposal preview shows revision notes and revise actions', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $proposal = Proposal::factory()->for($user)->for($client)->revised()->create([
        'revision_notes' => 'Please include an additional story and adjust budget details.',
    ]);

    $this->actingAs($user)
        ->get(route('proposals.show', $proposal))
        ->assertSuccessful()
        ->assertSee('Edit')
        ->assertSee('Send Again')
        ->assertSee('Revision Notes')
        ->assertSee('The client requested changes:')
        ->assertSee('Please include an additional story and adjust budget details.');
});
