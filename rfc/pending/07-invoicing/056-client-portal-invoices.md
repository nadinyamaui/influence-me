# 056 - Client Portal Invoices

**Labels:** `feature`, `invoicing`, `clients`, `ui`
**Depends on:** #035, #055

## Description

Create invoice pages in the client portal so clients can view their invoices and review status.

## Implementation

### Create Routes in `routes/portal.php`
```php
Route::livewire('portal/invoices', 'portal.invoices.index')
    ->middleware(['auth:client'])
    ->name('portal.invoices.index');

Route::livewire('portal/invoices/{invoice}', 'portal.invoices.show')
    ->middleware(['auth:client'])
    ->name('portal.invoices.show');
```

### Invoice List: `resources/views/pages/portal/invoices/index.blade.php`

**Table:**
| Column | Content |
|--------|---------|
| Invoice # | Links to detail |
| Status | Badge |
| Total | Currency formatted |
| Due Date | Date, red if overdue |
| Actions | View |

Filter by status (Sent, Paid, Overdue). Only show non-Draft invoices.
Implement filtering in the Livewire component query, not in Blade.

### Invoice Detail: `resources/views/pages/portal/invoices/show.blade.php`

Same professional layout as influencer view (#051) but:
- No edit/delete actions
- Clear unpaid status messaging when status is Sent/Overdue
- "Paid" confirmation if status is Paid (with paid_at date)
- Read-only view of all line items and totals

### Authorization
All queries scoped through authenticated ClientUser's client:
```php
$invoices = auth('client')->user()->client->invoices()
    ->whereNot('status', InvoiceStatus::Draft)
    ->latest()
    ->paginate();
```

### Update Portal Sidebar
Update `href="#"` for "Invoices" to `route('portal.invoices.index')`.

## Files to Create
- `resources/views/pages/portal/invoices/index.blade.php`
- `resources/views/pages/portal/invoices/show.blade.php`

## Files to Modify
- `routes/portal.php` — add routes
- `resources/views/layouts/portal/sidebar.blade.php` — update invoices link

## Acceptance Criteria
- [ ] Invoice list shows non-draft invoices for client
- [ ] Filter logic is implemented in the Livewire component query layer
- [ ] Invoice detail displays line items and totals
- [ ] No external payment link is exposed in portal invoice views
- [ ] Paid invoices show paid confirmation
- [ ] Data scoped to authenticated client
- [ ] Feature tests verify list, detail, and authorization
