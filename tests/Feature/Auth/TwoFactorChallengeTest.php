<?php

use App\Models\User;
use App\Services\Auth\FacebookSocialiteLoginService;
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

    $service = \Mockery::mock(FacebookSocialiteLoginService::class);
    $service->shouldReceive('createUserAndAccounts')
        ->once()
        ->andReturnUsing(function () use ($oauthUser) {
            auth()->login($oauthUser);

            return $oauthUser;
        });
    app()->instance(FacebookSocialiteLoginService::class, $service);

    $this->get(route('auth.facebook.callback'))
        ->assertRedirect(route('two-factor.login'))
        ->assertSessionHas('login.id', $oauthUser->id)
        ->assertSessionHas('login.remember', false);

    $this->assertGuest();

    $response = $this->get(route('two-factor.login'));

    $response->assertOk();
    $response->assertSee('Authentication Code');
});
