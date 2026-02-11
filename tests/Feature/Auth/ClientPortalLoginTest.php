<?php

use App\Models\ClientUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('portal login page renders for guest client users', function (): void {
    $response = $this->get(route('portal.login'));

    $response->assertOk()
        ->assertSee('Client Portal')
        ->assertSee('Log in to view your campaigns, proposals, and invoices')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertDontSee('Forgot your password?')
        ->assertDontSee('Need an account?');
});

test('client users can authenticate using portal login', function (): void {
    $clientUser = ClientUser::factory()->create();

    $response = $this->post(route('portal.login.store'), [
        'email' => $clientUser->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect('/portal/dashboard');

    expect(Auth::guard('client')->check())->toBeTrue()
        ->and(Auth::guard('client')->id())->toBe($clientUser->id)
        ->and(Auth::guard('web')->check())->toBeFalse();
});

test('client users cannot authenticate with invalid password', function (): void {
    $clientUser = ClientUser::factory()->create();

    $response = $this->post(route('portal.login.store'), [
        'email' => $clientUser->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    expect(Auth::guard('client')->check())->toBeFalse();
});

test('client users can logout from portal', function (): void {
    $clientUser = ClientUser::factory()->create();
    $this->actingAs($clientUser, 'client');

    $response = $this->post(route('portal.logout'));

    $response->assertRedirect(route('portal.login', absolute: false));

    expect(Auth::guard('client')->check())->toBeFalse();
});

test('portal login is rate limited to five attempts per minute per ip', function (): void {
    $clientUser = ClientUser::factory()->create();
    $payload = [
        'email' => $clientUser->email,
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        $response = $this->post(route('portal.login.store'), $payload);
        $response->assertSessionHasErrorsIn('email');
    }

    $sixthResponse = $this->post(route('portal.login.store'), $payload);
    $sixthResponse->assertStatus(429);
});

test('client and influencer guards remain isolated', function (): void {
    $user = User::factory()->create();
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($user, 'web');

    expect(Auth::guard('web')->check())->toBeTrue()
        ->and(Auth::guard('client')->check())->toBeFalse();

    $response = $this->post(route('portal.login.store'), [
        'email' => $clientUser->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/portal/dashboard');

    expect(Auth::guard('web')->check())->toBeTrue()
        ->and(Auth::guard('web')->id())->toBe($user->id)
        ->and(Auth::guard('client')->check())->toBeTrue()
        ->and(Auth::guard('client')->id())->toBe($clientUser->id);
});
