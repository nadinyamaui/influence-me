# 016 - Instagram OAuth Login Flow

**Labels:** `feature`, `authentication`
**Depends on:** #001, #003, #015

## Description

Implement the Instagram OAuth login flow through Meta/Facebook Login. Users click "Login with Instagram", are redirected to Meta authorization, authorize the app, and are redirected back. The system resolves the linked Instagram professional account, finds or creates a User and InstagramAccount, exchanges for a long-lived token, and logs the user in.

## Implementation

### Create `App\Http\Controllers\Auth\InstagramAuthController`

**`redirect()` method:**
- Redirect to Meta/Facebook OAuth with required scopes:
  `instagram_basic`, `instagram_manage_insights`, `pages_show_list`, `pages_read_engagement`, `business_management`
- Store `login` vs `add_account` intent outside OAuth `state` (session key) so Socialite state checks remain intact

**`callback()` method:**
1. Get the Meta/Facebook user from Socialite
2. Resolve the linked Instagram professional account through Graph API (`/me/accounts` + `instagram_business_account`)
3. Find existing `InstagramAccount` by `instagram_user_id`
4. If found: log in the associated User, update the access token
5. If not found: create a new `User` (name from IG, email nullable), create `InstagramAccount` (set as primary), log in
6. Exchange short-lived token for long-lived token (60 days) via Meta API
7. Store the long-lived token (encrypted) and `token_expires_at`
8. Redirect to `/dashboard`

### Token Exchange
Make HTTP call to:
```
GET https://graph.facebook.com/v23.0/oauth/access_token
  ?client_id={app-id}
  &client_secret={app-secret}
  &grant_type=fb_exchange_token
  &fb_exchange_token={short-lived-token}
```

### Update Login Page
Replace `resources/views/pages/auth/login.blade.php` with a single "Login with Instagram" button styled with Flux UI.

### Routes
```php
Route::get('/auth/instagram', [InstagramAuthController::class, 'redirect'])->name('auth.instagram');
Route::get('/auth/instagram/callback', [InstagramAuthController::class, 'callback'])->name('auth.instagram.callback');
```

Add routes to `routes/web.php`.

## Files to Create
- `app/Http/Controllers/Auth/InstagramAuthController.php`

## Files to Modify
- `routes/web.php` — add auth routes
- `resources/views/pages/auth/login.blade.php` — replace with Instagram button

## Acceptance Criteria
- [ ] Login page shows "Login with Instagram" button
- [ ] Clicking redirects to Meta/Facebook OAuth
- [ ] Successful callback creates User + InstagramAccount for new users
- [ ] Returning users are logged in and token refreshed
- [ ] Short-lived token exchanged for long-lived token
- [ ] Access token stored encrypted
- [ ] Callback fails with clear error when no Meta-linked Instagram professional account exists
- [ ] Redirects to dashboard after login
- [ ] Feature tests cover callback flow (mocked Socialite)
- [ ] Error handling for denied permissions or failed OAuth
