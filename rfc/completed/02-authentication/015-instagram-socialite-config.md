# 015 - Instagram Socialite Service Configuration

**Labels:** `feature`, `authentication`, `instagram`
**Depends on:** #014

## Description

Configure Laravel Socialite for Instagram Graph OAuth via Meta/Facebook Login. The `laravel/socialite` package is already installed. This issue only covers the configuration — not the login flow itself.

## Implementation

### Add Facebook config to `config/services.php`
```php
'facebook' => [
    'client_id' => env('META_CLIENT_ID'),
    'client_secret' => env('META_CLIENT_SECRET'),
    'redirect' => env('META_REDIRECT_URI'),
],
```

### Use built-in Facebook Socialite driver
Instagram Graph OAuth in this app uses Meta/Facebook Login, so no community Instagram provider is required.

### Verify `.env.example` has the variables
```
META_CLIENT_ID=
META_CLIENT_SECRET=
META_REDIRECT_URI=https://influence-me.test/auth/instagram/callback
```

## Files to Modify
- `config/services.php` — add `facebook` config block

## Files to Check/Verify
- `.env.example` — should already have variables from #014
- `composer.json` — verify `laravel/socialite`

## Acceptance Criteria
- [ ] Facebook service configured in `config/services.php`
- [ ] `Socialite::driver('facebook')` does not throw an error
- [ ] Environment variables in `.env.example`
