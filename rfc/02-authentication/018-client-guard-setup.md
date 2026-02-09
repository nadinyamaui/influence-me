# 018 - Client Authentication Guard Setup

**Labels:** `feature`, `authentication`, `security`
**Depends on:** #007

## Description

Configure Laravel's auth system to support a second guard for client portal access. Clients (ClientUser model) authenticate separately from influencers (User model) using email/password.

## Implementation

### Update `config/auth.php`

Add `client` guard:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'client' => [
        'driver' => 'session',
        'provider' => 'clients',
    ],
],
```

Add `clients` provider:
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'clients' => [
        'driver' => 'eloquent',
        'model' => App\Models\ClientUser::class,
    ],
],
```

Add `clients` password broker:
```php
'passwords' => [
    'users' => [/* existing */],
    'clients' => [
        'provider' => 'clients',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

### Update `bootstrap/app.php`

Add portal route file:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

### Create Portal Routes File
Create `routes/portal.php` and include it from `routes/web.php`:
```php
// In routes/web.php:
require __DIR__.'/portal.php';
```

`routes/portal.php` starts empty with just middleware group:
```php
Route::prefix('portal')->middleware(['guest:client'])->group(function () {
    // Login routes will go here in #019
});

Route::prefix('portal')->middleware(['auth:client'])->group(function () {
    // Authenticated portal routes will go here
});
```

## Files to Modify
- `config/auth.php`
- `routes/web.php` (include portal routes)

## Files to Create
- `routes/portal.php`

## Acceptance Criteria
- [ ] `config/auth.php` has `client` guard and `clients` provider
- [ ] `ClientUser` model works with the `client` guard
- [ ] Portal routes file is loaded
- [ ] Existing `web` guard still works for influencers
- [ ] Feature tests verify guard configuration
