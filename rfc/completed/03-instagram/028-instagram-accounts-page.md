# 028 - Instagram Accounts List Page

**Labels:** `feature`, `instagram`, `ui`
**Depends on:** #003, #013, #016

## Description

Create a Livewire page at `/instagram-accounts` that displays all connected Instagram accounts for the authenticated user. This is a read-only list — adding/disconnecting accounts is handled in #029.

## Implementation

### Create Route
In `routes/web.php`:
```php
Route::livewire('instagram-accounts', 'instagram-accounts.index')
    ->middleware(['auth'])
    ->name('instagram-accounts.index');
```

### Create Livewire Component
`resources/views/pages/instagram-accounts/index.blade.php` (full-page Livewire component)

### Page Content
Display a list/grid of connected accounts. For each account show:
- Profile picture (or initials avatar if no picture)
- Username (`@username`)
- Account type badge (Business/Creator)
- Primary badge (if is_primary)
- Followers count
- Media count
- Last synced timestamp (relative, e.g., "2 hours ago")
- Sync status indicator (idle = green, syncing = yellow spinner, failed = red)
- Token status (active/expired based on `token_expires_at`)

### Empty State
If no accounts connected, show a message:
"No Instagram accounts connected. Click below to connect your first account."
With a CTA button (links to `#` for now, implemented in #029).

### Update Sidebar
Update the sidebar `href="#"` for "Accounts" to use `route('instagram-accounts.index')`.

## Files to Create
- `resources/views/pages/instagram-accounts/index.blade.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update accounts link

## Acceptance Criteria
- [ ] Page renders at `/instagram-accounts`
- [ ] Lists all accounts for authenticated user
- [ ] Shows profile picture, username, type, follower count
- [ ] Shows sync status and last synced time
- [ ] Shows token expiry warning if within 7 days
- [ ] Empty state displayed when no accounts
- [ ] Sidebar link updated and active state works
- [ ] Feature test verifies page loads with account data
