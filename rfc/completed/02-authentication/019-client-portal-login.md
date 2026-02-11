# 019 - Client Portal Login Page

**Labels:** `feature`, `authentication`, `clients`, `ui`
**Depends on:** #007, #018

## Description

Build the client portal login and logout flow. Clients authenticate at `/portal/login` using email/password with the `client` guard.

## Implementation

### Create `App\Http\Controllers\Portal\PortalAuthController`

**`showLogin()` method:**
- Return the portal login view

**`login()` method:**
- Validate email + password
- Attempt auth with `client` guard: `Auth::guard('client')->attempt($credentials)`
- Regenerate session
- Redirect to `/portal/dashboard`
- Rate limit: 5 attempts per minute per IP

**`logout()` method:**
- `Auth::guard('client')->logout()`
- Invalidate session
- Regenerate token
- Redirect to `/portal/login`

### Create Login View
`resources/views/pages/portal/login.blade.php`

Use the existing auth layout (`<x-layouts::auth>`) but with:
- Different heading: "Client Portal"
- Subtitle: "Log in to view your campaigns, proposals, and invoices"
- Email + password form
- No "register" or "forgot password" links (accounts are created by influencers)

### Create Form Request
`App\Http\Requests\Portal\PortalLoginRequest` with:
- `email`: required, string, email
- `password`: required, string

### Routes in `routes/portal.php`
```php
Route::prefix('portal')->middleware(['guest:client'])->group(function () {
    Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [PortalAuthController::class, 'login'])->name('portal.login.store');
});

Route::prefix('portal')->middleware(['auth:client'])->group(function () {
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');
});
```

## Files to Create
- `app/Http/Controllers/Portal/PortalAuthController.php`
- `app/Http/Requests/Portal/PortalLoginRequest.php`
- `resources/views/pages/portal/login.blade.php`

## Files to Modify
- `routes/portal.php` — add routes

## Acceptance Criteria
- [ ] Portal login page renders at `/portal/login`
- [ ] Successful login redirects to `/portal/dashboard`
- [ ] Invalid credentials show error
- [ ] Logout works and redirects to login
- [ ] Rate limiting prevents brute force (5/min)
- [ ] Client guard session is separate from web guard
- [ ] Feature tests cover login, logout, and invalid credentials
