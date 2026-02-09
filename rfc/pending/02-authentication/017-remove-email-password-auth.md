# 017 - Remove Email/Password Authentication

**Labels:** `feature`, `authentication`
**Depends on:** #016

## Description

Since authentication is now Instagram-only, remove or disable the email/password auth features provided by Fortify. Keep 2FA as an optional additional security layer.

## Changes

### Update `config/fortify.php`
Remove these features:
```php
// Remove:
Features::registration(),
Features::resetPasswords(),
Features::emailVerification(),
```

Keep:
```php
Features::twoFactorAuthentication([
    'confirm' => true,
    'confirmPassword' => true,
]),
```

### Update `FortifyServiceProvider`
- Remove `CreateNewUser` action registration (no longer needed)
- Remove `Fortify::registerView()` and `Fortify::requestPasswordResetLinkView()` and `Fortify::resetPasswordView()`
- Keep `Fortify::loginView()` pointing to the updated Instagram login page
- Keep `Fortify::twoFactorChallengeView()`

### Remove Unused Files
- `app/Actions/Fortify/CreateNewUser.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/auth/forgot-password.blade.php`
- `resources/views/pages/auth/reset-password.blade.php`

### Update Settings Routes
- Remove password change route if desired (users don't have passwords)
- Keep profile settings (name, etc.)
- Keep 2FA settings
- Keep appearance settings

### Update `routes/settings.php`
Remove the `/settings/password` route since users authenticate via Instagram.

### Remove Unused Concerns
- `app/Concerns/PasswordValidationRules.php` — no longer needed
- Review `app/Concerns/ProfileValidationRules.php` — keep but remove email uniqueness check if email is no longer the primary identifier

## Files to Modify
- `config/fortify.php`
- `app/Providers/FortifyServiceProvider.php`
- `routes/settings.php`

## Files to Delete
- `app/Actions/Fortify/CreateNewUser.php`
- `app/Concerns/PasswordValidationRules.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/auth/forgot-password.blade.php`
- `resources/views/pages/auth/reset-password.blade.php`
- `resources/views/pages/settings/⚡password.blade.php`

## Acceptance Criteria
- [ ] Registration route returns 404
- [ ] Password reset route returns 404
- [ ] Login page only shows Instagram button
- [ ] 2FA still works after Instagram login
- [ ] Settings pages work without password route
- [ ] No dead links in navigation
- [ ] Existing auth tests updated or removed accordingly
