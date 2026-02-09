# 056 - Client Portal Invoices

**Labels:** `feature`, `invoicing`, `clients`, `ui`
**Depends on:** #035, #055

## Description

Create invoice pages in the client portal so clients can view their invoices and pay them.

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
| Actions | View, Pay (if unpaid + has payment link) |

Filter by status (Sent, Paid, Overdue). Only show non-Draft invoices.

### Invoice Detail: `resources/views/pages/portal/invoices/show.blade.php`

Same professional layout as influencer view (#051) but:
- No edit/delete actions
- Prominent "Pay Now" button if payment link exists and status is Sent/Overdue
- "Paid" confirmation if status is Paid (with paid_at date)
- Read-only view of all line items and totals

**Pay Now Button:**
```blade
@if($invoice->stripe_payment_link && $invoice->status->isUnpaid())
    <flux:button variant="primary" href="{{ $invoice->stripe_payment_link }}" target="_blank">
        Pay Now — ${{ number_format($invoice->total, 2) }}
    </flux:button>
@endif
```

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
- [ ] Invoice detail displays line items and totals
- [ ] "Pay Now" button links to Stripe payment page
- [ ] Paid invoices show paid confirmation
- [ ] Data scoped to authenticated client
- [ ] Feature tests verify list, detail, and authorization
