# 034 - Client Detail Page

**Labels:** `feature`, `clients`, `ui`
**Depends on:** #006, #012

## Description

Create a Livewire page at `/clients/{client}` showing an overview of a client with tabs for different sections. Tabs will start as placeholders and be populated by later issues.

## Implementation

### Create Route
```php
Route::livewire('clients/{client}', 'clients.show')
    ->middleware(['auth'])
    ->name('clients.show');
```

### Create Livewire Page
`resources/views/pages/clients/show.blade.php`

### Page Layout

**Header:**
- Client name (large)
- Type badge (Brand/Individual)
- Company name (if brand)
- Edit button (links to edit page)

**Client Info Card:**
- Email, Phone, Notes
- Portal access status: "Portal access: Active" or "No portal access" (with invite button — implemented in #036)

**Tabs (use Flux UI tabs):**
- **Overview** — summary stats (placeholder cards for now):
  - Total linked posts
  - Active proposals count
  - Pending invoices count/amount
- **Content** — linked Instagram content (placeholder, implemented in #041)
- **Proposals** — client's proposals (placeholder, implemented later)
- **Invoices** — client's invoices (placeholder, implemented later)
- **Analytics** — campaign analytics (placeholder, implemented in #064)

Each tab renders as a placeholder with "Coming soon" for now. Later issues will fill in the content.

## Files to Create
- `resources/views/pages/clients/show.blade.php`

## Files to Modify
- `routes/web.php` — add route

## Acceptance Criteria
- [ ] Page renders at `/clients/{client}`
- [ ] Authorization enforced (only owning user)
- [ ] Client info displayed correctly
- [ ] Tabs render with placeholder content
- [ ] Edit button links to edit page
- [ ] Feature test verifies page loads and authorization
