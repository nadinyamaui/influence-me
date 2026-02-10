<?php

use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;

it('resolves the facebook socialite driver for instagram graph auth', function () {
    config([
        'services.facebook.client_id' => 'test-client-id',
        'services.facebook.client_secret' => 'test-client-secret',
        'services.facebook.redirect' => 'https://influence-me.test/auth/instagram/callback',
    ]);

    $provider = Socialite::driver('facebook');

    expect($provider)->toBeInstanceOf(FacebookProvider::class);
});
