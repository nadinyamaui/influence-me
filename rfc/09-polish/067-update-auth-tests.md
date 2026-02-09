# 067 - Update Authentication Tests for Instagram OAuth

**Labels:** `testing`, `authentication`
**Depends on:** #016, #017

## Description

The existing auth tests in `tests/Feature/Auth/` were written for email/password authentication. They need to be updated or replaced to test the new Instagram OAuth flow.

## Tests to Update/Replace

### Remove or Replace
- `AuthenticationTest.php` — replace with Instagram OAuth tests
- `RegistrationTest.php` — remove (registration is via OAuth only)
- `PasswordResetTest.php` — remove (no password reset)
- `EmailVerificationTest.php` — remove (no email verification)
- `PasswordConfirmationTest.php` — review, may still be needed for 2FA

### Keep
- `TwoFactorChallengeTest.php` — update to work with OAuth users

### New Tests to Create
`tests/Feature/Auth/InstagramOAuthTest.php`:
- Test redirect to Instagram OAuth
- Test callback with new user (creates User + InstagramAccount)
- Test callback with returning user (logs in, updates token)
- Test callback with denied permissions (error handling)
- Test token exchange for long-lived token
- Test adding additional account for authenticated user
- Test logout

### Mocking Socialite
```php
Socialite::shouldReceive('driver->redirect')->andReturn(/* redirect response */);
Socialite::shouldReceive('driver->user')->andReturn(/* fake Instagram user */);
```

## Files to Create
- `tests/Feature/Auth/InstagramOAuthTest.php`

## Files to Modify/Delete
- `tests/Feature/Auth/AuthenticationTest.php` — delete
- `tests/Feature/Auth/RegistrationTest.php` — delete
- `tests/Feature/Auth/PasswordResetTest.php` — delete
- `tests/Feature/Auth/EmailVerificationTest.php` — delete
- `tests/Feature/Auth/PasswordConfirmationTest.php` — review
- `tests/Feature/Auth/TwoFactorChallengeTest.php` — update

## Files to Update
- `tests/Feature/Settings/PasswordUpdateTest.php` — delete (no passwords)

## Acceptance Criteria
- [ ] All old email/password tests removed
- [ ] New OAuth tests cover full login flow
- [ ] Socialite properly mocked
- [ ] 2FA tests updated and passing
- [ ] `php artisan test` passes with zero failures
