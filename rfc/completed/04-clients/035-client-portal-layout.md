# 035 - Client Portal Layout

**Labels:** `feature`, `clients`, `ui`
**Depends on:** #019

## Description

Create a dedicated layout for the client portal. Similar to the main app layout but with distinct branding and simplified navigation for clients.

## Implementation

### Create Portal Layout
`resources/views/layouts/portal.blade.php`

Based on the existing `app.blade.php` pattern but with:
- Different branding/heading: "Okacrm — Client Portal"
- Simplified sidebar with only client-relevant items:
  - Dashboard (icon: `home`)
  - Proposals (icon: `document-text`)
  - Invoices (icon: `banknotes`)
  - Analytics (icon: `chart-bar`)
- User menu showing client name and logout
- All nav items use `href="#"` placeholders (routes added in later issues)
- No settings or Instagram-specific navigation

### Create Portal Sidebar
`resources/views/layouts/portal/sidebar.blade.php`

Follow the same Flux UI sidebar pattern as the main app but with the portal-specific items.

### Create Portal Dashboard Placeholder
`resources/views/pages/portal/dashboard.blade.php`

Simple placeholder page using the portal layout:
```blade
<x-layouts::portal :title="__('Dashboard')">
    <flux:main>
        <div>Portal Dashboard (coming soon)</div>
    </flux:main>
</x-layouts::portal>
```

### Route
```php
Route::prefix('portal')->middleware(['auth:client'])->group(function () {
    Route::view('/dashboard', 'pages.portal.dashboard')->name('portal.dashboard');
});
```

## Files to Create
- `resources/views/layouts/portal.blade.php`
- `resources/views/layouts/portal/sidebar.blade.php`
- `resources/views/pages/portal/dashboard.blade.php`

## Files to Modify
- `routes/portal.php` — add dashboard route

## Acceptance Criteria
- [ ] Portal layout renders with distinct branding
- [ ] Sidebar shows only client-relevant navigation
- [ ] Portal dashboard renders at `/portal/dashboard`
- [ ] Only accessible with `client` guard (redirects to portal login otherwise)
- [ ] User menu shows client name and logout works
- [ ] Feature test verifies layout renders for authenticated client
