<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('portal dashboard requires authenticated client guard', function (): void {
    $this->get(route('portal.dashboard'))
        ->assertRedirect(route('portal.login'));
});

test('authenticated client users can view the portal dashboard layout', function (): void {
    $client = Client::factory()->create();
    $clientUser = ClientUser::factory()->for($client)->create([
        'name' => 'Avery Client',
        'email' => 'avery@example.test',
    ]);

    $this->actingAs($clientUser, 'client')
        ->get(route('portal.dashboard'))
        ->assertSuccessful()
        ->assertSee('Influence Me - Client Portal')
        ->assertSee('Portal Dashboard (coming soon)')
        ->assertSee('Dashboard')
        ->assertSee('Proposals')
        ->assertSee('Invoices')
        ->assertSee('Analytics')
        ->assertSee('Avery Client')
        ->assertSee('href="'.route('portal.dashboard').'"', false)
        ->assertSee('action="'.route('portal.logout').'"', false);
});

test('authenticated influencer users are redirected to client portal login', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get(route('portal.dashboard'))
        ->assertRedirect(route('portal.login'));

    expect(Auth::guard('web')->check())->toBeTrue()
        ->and(Auth::guard('client')->check())->toBeFalse();
});

test('client users can log out from the portal dashboard', function (): void {
    $clientUser = ClientUser::factory()->create();
    $this->actingAs($clientUser, 'client');

    $this->post(route('portal.logout'))
        ->assertRedirect(route('portal.login', absolute: false));

    expect(Auth::guard('client')->check())->toBeFalse();
});
