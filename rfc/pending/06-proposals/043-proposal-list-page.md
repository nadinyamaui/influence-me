# 043 - Proposal List Page

**Labels:** `feature`, `proposals`, `ui`
**Depends on:** #008, #012, #013

## Description

Create a Livewire page at `/proposals` that displays all proposals for the authenticated influencer with filtering by status and client.

## Implementation

### Create Route
```php
Route::livewire('proposals', 'proposals.index')
    ->middleware(['auth'])
    ->name('proposals.index');
```

### Create Livewire Page
`resources/views/pages/proposals/index.blade.php`

### Page Content

**Header:** "Proposals" title with "New Proposal" button (links to create page)

**Filters:**
- Status: All, Draft, Sent, Approved, Rejected, Revised (Flux UI select)
- Client: dropdown of user's clients (or All)
- Apply filters in the Livewire component query builder (not in Blade)

**Table:**
| Column | Content |
|--------|---------|
| Title | Proposal title (links to detail) |
| Client | Client name |
| Status | Color badge (Draft=gray, Sent=blue, Approved=green, Rejected=red, Revised=amber) |
| Created | Date created |
| Last Updated | Date updated |
| Actions | View, Edit (draft only), Delete |

**Pagination:** Standard Laravel pagination

**Empty State:** "No proposals yet. Create your first proposal to send to a client."

### Update Sidebar
Update sidebar `href="#"` for "Proposals" to `route('proposals.index')`.

## Files to Create
- `resources/views/pages/proposals/index.blade.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update proposals link

## Acceptance Criteria
- [ ] Page renders at `/proposals`
- [ ] Lists only proposals belonging to authenticated user
- [ ] Status filter works with correct badge colors
- [ ] Client filter works
- [ ] Filter logic is implemented in the Livewire component query layer
- [ ] Pagination works
- [ ] Empty state shown when no proposals
- [ ] Feature test verifies list and filters
