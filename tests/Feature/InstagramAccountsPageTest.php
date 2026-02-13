<?php

use App\Enums\AccountType;
use App\Enums\SyncStatus;
use App\Models\InstagramAccount;
use App\Models\User;
use Carbon\Carbon;

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
        'account_type' => AccountType::Creator,
        'is_primary' => true,
        'followers_count' => 12345,
        'media_count' => 87,
        'last_synced_at' => now()->subHours(2),
        'token_expires_at' => now()->addDays(3),
        'sync_status' => SyncStatus::Syncing,
    ]);

    InstagramAccount::factory()->for($user)->create([
        'username' => 'brandaccount',
        'account_type' => AccountType::Business,
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
        ->assertSee('Creator')
        ->assertSee('Business')
        ->assertSee('12,345')
        ->assertSee('87')
        ->assertSee('Syncing')
        ->assertSee('Idle')
        ->assertSee('Expires within 7 days')
        ->assertSee('Active')
        ->assertSee('2 hours ago')
        ->assertSee('1 day ago');

    Carbon::setTestNow();
});

test('instagram accounts page shows empty state and connect call to action', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('instagram-accounts.index'));

    $response->assertSuccessful()
        ->assertSee('No Instagram accounts connected.')
        ->assertSee('Click below to connect your first account.')
        ->assertSee('Connect Instagram Account')
        ->assertSee('href="#"', false);
});
