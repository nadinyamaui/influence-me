# 029 - Connect and Disconnect Instagram Accounts

**Labels:** `feature`, `instagram`
**Depends on:** #016, #028

## Description

Add the ability to connect additional Instagram accounts and disconnect existing ones from the Instagram Accounts page.

## Implementation

### Connect Additional Account
Reuse the existing OAuth flow from #016 but with a different state parameter to distinguish "login" from "add account".

**Update `InstagramAuthController`:**
- Add `addAccount()` method that redirects to Instagram OAuth with `state=add_account`
- Update `callback()` to check state:
  - If `state=add_account` AND user is already authenticated:
    - Create new `InstagramAccount` linked to current user
    - Do NOT create a new User
    - Redirect back to `/instagram-accounts` with success message
  - If `state=login` (default): existing login flow

**Route:**
```php
Route::get('/auth/instagram/add', [InstagramAuthController::class, 'addAccount'])
    ->middleware('auth')
    ->name('auth.instagram.add');
```

### Disconnect Account
**Add Livewire action on accounts page:**
- Wire action `disconnect(InstagramAccount $account)`
- Verify policy: user owns the account
- Cannot disconnect if it's the only account
- Delete the `InstagramAccount` record
- Show confirmation modal before disconnect

### Set Primary Account
**Add Livewire action:**
- Wire action `setPrimary(InstagramAccount $account)`
- Set `is_primary = false` on all user's accounts
- Set `is_primary = true` on selected account

## Files to Modify
- `app/Http/Controllers/Auth/InstagramAuthController.php` — add `addAccount()`, update `callback()`
- `routes/web.php` — add route
- `resources/views/pages/instagram-accounts/index.blade.php` — add buttons and actions

## Acceptance Criteria
- [ ] "Connect Another Account" button triggers OAuth for adding
- [ ] Callback creates new InstagramAccount for existing user
- [ ] Cannot disconnect last remaining account (shows error)
- [ ] Disconnect with confirmation modal works
- [ ] Set primary account works
- [ ] Feature tests cover add, disconnect, and set primary
