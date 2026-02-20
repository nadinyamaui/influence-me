<?php

use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\TikTok\Provider as TikTokProvider;

it('resolves the tiktok socialite driver with expected service configuration', function (): void {
    config([
        'services.tiktok.client_id' => 'tiktok-client-id',
        'services.tiktok.client_secret' => 'tiktok-client-secret',
        'services.tiktok.redirect' => 'https://influence-me.test/auth/tiktok/callback',
    ]);

    expect(config('services.tiktok'))->toBe([
        'client_id' => 'tiktok-client-id',
        'client_secret' => 'tiktok-client-secret',
        'redirect' => 'https://influence-me.test/auth/tiktok/callback',
    ]);

    $provider = Socialite::driver('tiktok');

    expect($provider)->toBeInstanceOf(TikTokProvider::class);
});
