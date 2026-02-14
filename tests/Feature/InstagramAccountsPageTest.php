<?php

use App\Enums\SyncStatus;
use App\Jobs\SyncAllInstagramData;
use App\Livewire\InstagramAccounts\Index;
use App\Models\InstagramAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

test('guests are redirected to login from instagram accounts page', function (): void {
    $this->get(route('instagram-accounts.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can see their instagram accounts with statuses and token warnings', function (): void {
    Carbon::setTestNow('2026-02-13 12:00:00');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    InstagramAccount::factory()->for($user)->create([
        'username' => 'primarycreator',
        'profile_picture_url' => null,
        'is_primary' => true,
        'followers_count' => 12345,
        'media_count' => 87,
        'last_synced_at' => now()->subHours(2),
        'token_expires_at' => now()->addDays(3),
        'sync_status' => SyncStatus::Syncing,
    ]);

    InstagramAccount::factory()->for($user)->create([
        'username' => 'brandaccount',
        'followers_count' => 980,
        'media_count' => 44,
        'last_synced_at' => now()->subDay(),
        'token_expires_at' => now()->addDays(30),
        'sync_status' => SyncStatus::Idle,
    ]);

    InstagramAccount::factory()->for($otherUser)->create([
        'username' => 'hiddenaccount',
    ]);

    $response = $this->actingAs($user)->get(route('instagram-accounts.index'));

    $response->assertSuccessful()
        ->assertSee('Instagram Accounts')
        ->assertSee('@primarycreator')
        ->assertSee('@brandaccount')
        ->assertDontSee('@hiddenaccount')
        ->assertSee('Primary')
        ->assertSee('12,345')
        ->assertSee('87')
        ->assertSee('Syncing...')
        ->assertSee('Up to date')
        ->assertSee('Expires within 7 days')
        ->assertSee('Re-authenticate')
        ->assertSee('Active')
        ->assertSee('2 hours ago')
        ->assertSee('1 day ago')
        ->assertSee('wire:poll.5s', false);

    Carbon::setTestNow();
});

test('instagram accounts page shows empty state and connect call to action', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('instagram-accounts.index'));

    $response->assertSuccessful()
        ->assertSee('No Instagram accounts connected.')
        ->assertSee('Click below to connect your first account.')
        ->assertSee('Connect Instagram Account')
        ->assertSee(route('auth.facebook.add'));
});

test('authenticated users can set a non-primary account as primary', function (): void {
    $user = User::factory()->create();

    $primaryAccount = InstagramAccount::factory()
        ->for($user)
        ->primary()
        ->create();
    $secondaryAccount = InstagramAccount::factory()
        ->for($user)
        ->create(['is_primary' => false]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('setPrimary', $secondaryAccount->id);

    expect($primaryAccount->fresh()->is_primary)->toBeFalse()
        ->and($secondaryAccount->fresh()->is_primary)->toBeTrue();
});

test('authenticated users can disconnect an account after confirmation', function (): void {
    $user = User::factory()->create();

    $firstAccount = InstagramAccount::factory()
        ->for($user)
        ->primary()
        ->create();
    $secondAccount = InstagramAccount::factory()
        ->for($user)
        ->create(['is_primary' => false]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('confirmDisconnect', $secondAccount->id)
        ->assertSet('disconnectingAccountId', $secondAccount->id)
        ->call('disconnect')
        ->assertSet('disconnectingAccountId', null);

    $this->assertDatabaseMissing('instagram_accounts', ['id' => $secondAccount->id]);
    $this->assertDatabaseHas('instagram_accounts', ['id' => $firstAccount->id]);
});

test('users cannot disconnect their last instagram account', function (): void {
    $user = User::factory()->create();

    $account = InstagramAccount::factory()
        ->for($user)
        ->primary()
        ->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('confirmDisconnect', $account->id)
        ->assertHasErrors(['disconnect'])
        ->assertSet('disconnectingAccountId', null);

    $this->assertDatabaseHas('instagram_accounts', ['id' => $account->id]);
});

test('users can manually trigger sync from instagram accounts page', function (): void {
    Bus::fake();

    $user = User::factory()->create();
    $account = InstagramAccount::factory()
        ->for($user)
        ->create([
            'sync_status' => SyncStatus::Idle,
            'last_sync_error' => 'stale error',
        ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('syncNow', $account->id)
        ->assertSee('Syncing...');

    $account->refresh();

    expect($account->sync_status)->toBe(SyncStatus::Syncing)
        ->and($account->last_sync_error)->toBeNull();

    Bus::assertDispatched(SyncAllInstagramData::class, fn (SyncAllInstagramData $job): bool => $job->account->is($account));
});

test('manual sync action does not dispatch when account is already syncing', function (): void {
    Bus::fake();

    $user = User::factory()->create();
    $account = InstagramAccount::factory()
        ->for($user)
        ->create([
            'sync_status' => SyncStatus::Syncing,
        ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('syncNow', $account->id);

    Bus::assertNotDispatched(SyncAllInstagramData::class);
});

test('failed sync status shows collapsible sync error details', function (): void {
    $user = User::factory()->create();

    InstagramAccount::factory()
        ->for($user)
        ->create([
            'sync_status' => SyncStatus::Failed,
            'last_sync_error' => 'Instagram API unavailable',
        ]);

    $this->actingAs($user)
        ->get(route('instagram-accounts.index'))
        ->assertSuccessful()
        ->assertSee('Sync failed')
        ->assertSee('View sync error')
        ->assertSee('Instagram API unavailable');
});
