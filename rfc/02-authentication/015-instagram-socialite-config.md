# 015 - Instagram Socialite Service Configuration

**Labels:** `feature`, `authentication`, `instagram`
**Depends on:** #014

## Description

Configure Laravel Socialite for Instagram OAuth. The `laravel/socialite` package is already installed. This issue only covers the configuration — not the login flow itself.

## Implementation

### Add Instagram config to `config/services.php`
```php
'instagram' => [
    'client_id' => env('INSTAGRAM_CLIENT_ID'),
    'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
    'redirect' => env('INSTAGRAM_REDIRECT_URI'),
],
```

### Install Socialite Instagram Provider
Instagram Graph API is not a built-in Socialite driver. Install the community provider:
```
composer require socialiteproviders/instagram
```

Register the provider event listener. In Laravel 12, this goes in `AppServiceProvider` or a dedicated listener:
```php
// In AppServiceProvider boot():
Event::listen(
    SocialiteWasCalled::class,
    \SocialiteProviders\Instagram\InstagramExtendSocialite::class . '@handle'
);
```

### Verify `.env.example` has the variables
```
INSTAGRAM_CLIENT_ID=
INSTAGRAM_CLIENT_SECRET=
INSTAGRAM_REDIRECT_URI=https://influence-me.test/auth/instagram/callback
```

## Files to Modify
- `config/services.php` — add `instagram` config block
- `app/Providers/AppServiceProvider.php` — register Socialite event listener

## Files to Check/Verify
- `.env.example` — should already have variables from #014
- `composer.json` — verify `laravel/socialite` and `socialiteproviders/instagram`

## Acceptance Criteria
- [ ] Instagram service configured in `config/services.php`
- [ ] Socialite provider registered and autoloaded
- [ ] `Socialite::driver('instagram')` does not throw an error
- [ ] Environment variables in `.env.example`
