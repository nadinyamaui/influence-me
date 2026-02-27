# 051 - Invoice Preview/Detail Page

**Labels:** `feature`, `invoicing`, `ui`
**Depends on:** #050

## Description

Create a Livewire page at `/invoices/{invoice}` showing a professional invoice layout. Also serves as the edit page for draft invoices.

## Implementation

### Create Routes
```php
Route::livewire('invoices/{invoice}', 'invoices.show')
    ->middleware(['auth'])
    ->name('invoices.show');

Route::livewire('invoices/{invoice}/edit', 'invoices.edit')
    ->middleware(['auth'])
    ->name('invoices.edit');
```

### Invoice Detail: `resources/views/pages/invoices/show.blade.php`

**Header:**
- Invoice number (large)
- Status badge
- Action buttons (conditional by status)

**Invoice Layout (professional format):**
```
┌─────────────────────────────────────────┐
│  INVOICE                    INV-2026-001│
│                                         │
│  From: {influencer name}                │
│  To: {client name}                      │
│      {client company}                   │
│      {client email}                     │
│                                         │
│  Date: {created date}                   │
│  Due: {due date}                        │
│  Status: {badge}                        │
│                                         │
│  ─────────────────────────────────────  │
│  Description    Qty   Price     Total   │
│  ─────────────────────────────────────  │
│  Item 1          2   $100.00   $200.00  │
│  Item 2          1    $50.00    $50.00  │
│  ─────────────────────────────────────  │
│                     Subtotal   $250.00  │
│                     Tax (10%)   $25.00  │
│                     Total      $275.00  │
│                                         │
│  Notes: {notes}                         │
│                                         │
│  Payment Link: {link if exists}         │
└─────────────────────────────────────────┘
```

**Action Buttons:**
- Draft: "Edit", "Send to Client", "Delete"
- Sent: "Generate Payment Link" (if no link yet), "Resend"
- Paid: "Paid" badge with paid date
- Overdue: "Send Reminder", "Generate Payment Link"

### Edit Page: `resources/views/pages/invoices/edit.blade.php`
- Same form as create (#050), pre-filled
- Only editable when status is Draft
- Non-draft: redirect to show page

## Files to Create
- `resources/views/pages/invoices/show.blade.php`
- `resources/views/pages/invoices/edit.blade.php`

## Files to Modify
- `routes/web.php` — add routes

## Acceptance Criteria
- [ ] Invoice detail renders professional layout
- [ ] Line items displayed in table format
- [ ] Totals calculated and displayed correctly
- [ ] Status-appropriate action buttons shown
- [ ] Edit only works for Draft invoices
- [ ] Delete with confirmation (Draft only)
- [ ] Authorization enforced
- [ ] Feature tests cover display and edit restrictions
