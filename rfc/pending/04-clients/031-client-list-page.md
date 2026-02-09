# 031 - Client List Page

**Labels:** `feature`, `clients`, `ui`
**Depends on:** #006, #012, #013

## Description

Create a Livewire page at `/clients` that displays all clients for the authenticated influencer. Includes search and filtering.

## Implementation

### Create Route
In `routes/web.php`:
```php
Route::livewire('clients', 'clients.index')
    ->middleware(['auth'])
    ->name('clients.index');
```

### Create Livewire Page
`resources/views/pages/clients/index.blade.php` (full-page Livewire component)

### Page Content

**Header:** "Clients" title with "Add Client" button (links to create page)

**Search & Filters:**
- Search input: filters by name, email, or company_name (debounced, `wire:model.live.debounce.300ms`)
- Type filter: dropdown with All, Brand, Individual

**Table/List:**
| Column | Content |
|--------|---------|
| Name | Client name |
| Company | Company name (or "—" if individual) |
| Type | Badge: Brand (blue) or Individual (green) |
| Email | Client email |
| Campaigns | Count of linked media |
| Actions | View, Edit, Delete buttons |

**Pagination:** Use Laravel pagination with Livewire

**Empty State:** "No clients yet. Add your first client to start managing campaigns."

### Update Sidebar
Update sidebar `href="#"` for "Clients" to `route('clients.index')`.

## Files to Create
- `resources/views/pages/clients/index.blade.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update clients link

## Acceptance Criteria
- [ ] Page renders at `/clients`
- [ ] Lists only clients belonging to authenticated user
- [ ] Search filters by name, email, company
- [ ] Type filter works
- [ ] Pagination works
- [ ] Empty state shown when no clients
- [ ] Sidebar link active state works
- [ ] Feature test verifies list, search, and filter
