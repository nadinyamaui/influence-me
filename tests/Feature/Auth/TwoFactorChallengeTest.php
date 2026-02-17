<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('two factor challenge redirects to login when challenge session is missing', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $response = $this->get(route('two-factor.login'));

    $response->assertRedirect(route('login'));
});

test('two factor challenge can be rendered for oauth user pending challenge', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $oauthUser = User::factory()->withTwoFactor()->create([
        'socialite_user_type' => 'facebook',
        'socialite_user_id' => '1234567890123',
    ]);

    $response = $this->withSession([
        'login.id' => $oauthUser->id,
        'login.remember' => false,
    ])->get(route('two-factor.login'));

    $response->assertOk();
    $response->assertSee('Authentication Code');
});
