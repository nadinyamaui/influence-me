<?php

use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Instagram\Provider as InstagramProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

it('registers the instagram socialite provider listener', function () {
    expect(Event::hasListeners(SocialiteWasCalled::class))->toBeTrue();
});

it('resolves the instagram socialite driver', function () {
    config([
        'services.instagram.client_id' => 'test-client-id',
        'services.instagram.client_secret' => 'test-client-secret',
        'services.instagram.redirect' => 'https://influence-me.test/auth/instagram/callback',
    ]);

    $provider = Socialite::driver('instagram');

    expect($provider)->toBeInstanceOf(InstagramProvider::class);
});
