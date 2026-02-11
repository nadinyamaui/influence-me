# 016 - Instagram OAuth Login Flow

**Labels:** `feature`, `authentication`
**Depends on:** #001, #003, #015

## Description

Implement the Instagram OAuth login flow. Users click "Login with Instagram", are redirected to Instagram, authorize the app, and are redirected back. The system finds or creates a User and InstagramAccount, exchanges for a long-lived token, and logs the user in.

## Implementation

### Create `App\Http\Controllers\Auth\InstagramAuthController`

**`redirect()` method:**
- Redirect to Instagram OAuth with required scopes:
  `instagram_basic`, `instagram_manage_insights`, `pages_show_list`, `pages_read_engagement`
- Add state parameter to distinguish "login" vs "add account" (for issue #029)

**`callback()` method:**
1. Get the Instagram user from Socialite
2. Find existing `InstagramAccount` by `instagram_user_id`
3. If found: log in the associated User, update the access token
4. If not found: create a new `User` (name from IG, email nullable), create `InstagramAccount` (set as primary), log in
5. Exchange short-lived token for long-lived token (60 days) via Meta API
6. Store the long-lived token (encrypted) and `token_expires_at`
7. Redirect to `/dashboard`

### Token Exchange
Make HTTP call to:
```
GET https://graph.instagram.com/access_token
  ?grant_type=ig_exchange_token
  &client_secret={app-secret}
  &access_token={short-lived-token}
```

### Update Login Page
Replace `resources/views/pages/auth/login.blade.php` with a single "Login with Instagram" button styled with Flux UI.

### Routes
```php
Route::get('/auth/instagram', [InstagramAuthController::class, 'redirect'])->name('auth.instagram');
Route::get('/auth/facebook/callback', [InstagramAuthController::class, 'callback'])->name('auth.instagram.callback');
```

Add routes to `routes/web.php`.

## Files to Create
- `app/Http/Controllers/Auth/InstagramAuthController.php`

## Files to Modify
- `routes/web.php` — add auth routes
- `resources/views/pages/auth/login.blade.php` — replace with Instagram button

## Acceptance Criteria
- [ ] Login page shows "Login with Instagram" button
- [ ] Clicking redirects to Instagram OAuth
- [ ] Successful callback creates User + InstagramAccount for new users
- [ ] Returning users are logged in and token refreshed
- [ ] Short-lived token exchanged for long-lived token
- [ ] Access token stored encrypted
- [ ] Redirects to dashboard after login
- [ ] Feature tests cover callback flow (mocked Socialite)
- [ ] Error handling for denied permissions or failed OAuth
