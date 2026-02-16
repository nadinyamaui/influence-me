# 049 - Invoice List Page

**Labels:** `feature`, `invoicing`, `ui`
**Depends on:** #009, #012, #013

## Description

Create a Livewire page at `/invoices` that displays all invoices for the authenticated influencer with filtering by status.

## Implementation

### Create Route
```php
Route::livewire('invoices', 'invoices.index')
    ->middleware(['auth'])
    ->name('invoices.index');
```

### Create Livewire Page
`resources/views/pages/invoices/index.blade.php`

### Page Content

**Header:** "Invoices" title with "New Invoice" button

**Summary Cards (top):**
- Total Outstanding: sum of unpaid invoices (Sent + Overdue)
- Paid This Month: sum of invoices paid in current month
- Overdue: count of overdue invoices

**Filters:**
- Status: All, Draft, Sent, Paid, Overdue, Cancelled
- Client: dropdown of user's clients
- Apply filters in the Livewire component query builder (not in Blade)

**Table:**
| Column | Content |
|--------|---------|
| Invoice # | INV-2026-0001 format (links to detail) |
| Client | Client name |
| Status | Badge (Draft=gray, Sent=blue, Paid=green, Overdue=red, Cancelled=gray) |
| Total | Formatted currency |
| Due Date | Date, highlight in red if overdue |
| Actions | View, Edit (draft), Delete (draft) |

**Pagination:** Standard pagination

**Empty State:** "No invoices yet. Create your first invoice."

### Update Sidebar
Update sidebar `href="#"` for "Invoices" to `route('invoices.index')`.

## Files to Create
- `resources/views/pages/invoices/index.blade.php`

## Files to Modify
- `routes/web.php` — add route
- `resources/views/layouts/app/sidebar.blade.php` — update invoices link

## Acceptance Criteria
- [ ] Page renders at `/invoices`
- [ ] Summary cards show correct totals
- [ ] Status filter works with badge colors
- [ ] Client filter works
- [ ] Filter logic is implemented in the Livewire component query layer
- [ ] Due dates highlighted when overdue
- [ ] Pagination works
- [ ] Empty state shown
- [ ] Feature test verifies list and filters
