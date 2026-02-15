<?php

use App\Enums\ProposalStatus;
use App\Livewire\Proposals\Show;
use App\Mail\ProposalSent;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\Proposal;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('influencer can send a draft proposal and client receives email', function (): void {
    Mail::fake();

    $owner = User::factory()->create(['name' => 'Nadin Creator']);
    $client = Client::factory()->for($owner)->create([
        'name' => 'Acme Client',
        'email' => 'acme@example.test',
    ]);

    $proposal = Proposal::factory()->for($owner)->for($client)->draft()->create([
        'title' => 'Spring Campaign',
        'content' => 'This is the campaign plan with platform deliverables and expected timeline.',
    ]);

    $campaign = Campaign::factory()->for($client)->for($proposal)->create();
    $account = InstagramAccount::factory()->for($owner)->create();

    ScheduledPost::factory()->for($owner)->for($client)->create([
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['proposal' => $proposal])
        ->call('confirmSend')
        ->assertSet('confirmingSend', true)
        ->assertSee('Send this proposal to Acme Client at acme@example.test?')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('confirmingSend', false);

    $proposal->refresh();

    expect($proposal->status)->toBe(ProposalStatus::Sent)
        ->and($proposal->sent_at)->not->toBeNull();

    Mail::assertSent(ProposalSent::class, function (ProposalSent $mail) use ($proposal): bool {
        $rendered = $mail->render();

        return $mail->hasTo('acme@example.test')
            && str_contains($rendered, 'Nadin Creator')
            && str_contains($rendered, $proposal->title);
    });
});

test('influencer can send a revised proposal', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create([
        'email' => 'client@example.test',
    ]);
    $proposal = Proposal::factory()->for($owner)->for($client)->revised()->create();
    $campaign = Campaign::factory()->for($client)->for($proposal)->create();
    $account = InstagramAccount::factory()->for($owner)->create();

    ScheduledPost::factory()->for($owner)->for($client)->create([
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['proposal' => $proposal])
        ->call('confirmSend')
        ->assertSet('confirmingSend', true)
        ->call('send')
        ->assertHasNoErrors();

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Sent);
    Mail::assertSent(ProposalSent::class);
});

test('proposal cannot be sent when client email is missing', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create(['email' => null]);
    $proposal = Proposal::factory()->for($owner)->for($client)->draft()->create();
    $campaign = Campaign::factory()->for($client)->for($proposal)->create();
    $account = InstagramAccount::factory()->for($owner)->create();

    ScheduledPost::factory()->for($owner)->for($client)->create([
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['proposal' => $proposal])
        ->call('confirmSend')
        ->assertHasErrors(['send'])
        ->assertSet('confirmingSend', false);

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Draft);
    Mail::assertNothingSent();
});

test('proposal cannot be sent when no campaign is linked', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create(['email' => 'client@example.test']);
    $proposal = Proposal::factory()->for($owner)->for($client)->draft()->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['proposal' => $proposal])
        ->call('confirmSend')
        ->assertHasErrors(['send'])
        ->assertSet('confirmingSend', false);

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Draft);
    Mail::assertNothingSent();
});

test('proposal cannot be sent when any linked campaign has no scheduled content', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $client = Client::factory()->for($owner)->create(['email' => 'client@example.test']);
    $proposal = Proposal::factory()->for($owner)->for($client)->draft()->create();

    Campaign::factory()->for($client)->for($proposal)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['proposal' => $proposal])
        ->call('confirmSend')
        ->assertHasErrors(['send'])
        ->assertSet('confirmingSend', false);

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Draft);
    Mail::assertNothingSent();
});

test('proposal cannot be sent when linked scheduled content has mismatched scope', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $client = Client::factory()->for($owner)->create(['email' => 'client@example.test']);
    $otherClient = Client::factory()->for($otherUser)->create();
    $proposal = Proposal::factory()->for($owner)->for($client)->draft()->create();
    $campaign = Campaign::factory()->for($client)->for($proposal)->create();
    $account = InstagramAccount::factory()->for($otherUser)->create();

    ScheduledPost::factory()->for($otherUser)->for($otherClient)->create([
        'campaign_id' => $campaign->id,
        'instagram_account_id' => $account->id,
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['proposal' => $proposal])
        ->call('confirmSend')
        ->assertHasErrors(['send'])
        ->assertSet('confirmingSend', false);

    expect($proposal->fresh()->status)->toBe(ProposalStatus::Draft);
    Mail::assertNothingSent();
});
