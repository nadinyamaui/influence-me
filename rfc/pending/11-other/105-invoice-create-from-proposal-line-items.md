# 105 - Invoice Create from Approved Proposal Line Items

**Labels:** `feature`, `invoicing`, `proposals`
**Depends on:** #050, #051, #103, #104

## Description

Support manual invoice creation from approved proposal pricing snapshots.

## Implementation

### Invoice Create Enhancements
On invoice create page:
- Select client.
- Optional: select one approved proposal for that client.
- Import selected proposal line items into invoice items.
- Allow influencer to adjust imported rows before save.

### Data Rules
- Import uses proposal snapshots as defaults (not live catalog prices).
- Save source traceability on invoice item (proposal line item id + source metadata).
- Invoice totals continue using existing invoice calculation logic.

### Service Layer
Add `InvoiceFromProposalService`:
- validates ownership and proposal status
- maps proposal line item snapshots into invoice draft item payloads

## Files to Create
- `app/Services/Invoices/InvoiceFromProposalService.php`

## Files to Modify
- invoice create Livewire component and Blade (RFC #050 files)
- `app/Http/Requests/StoreInvoiceRequest.php`
- `app/Models/InvoiceItem.php` (source metadata casts/attributes)
- invoice-related migrations for item source fields

## Acceptance Criteria
- [ ] Approved proposal line items can be imported into invoice draft
- [ ] Partial import selection works
- [ ] Imported rows preserve snapshot pricing by default
- [ ] Influencer can edit rows before saving invoice
- [ ] Authorization prevents cross-user proposal import
- [ ] Feature tests cover import and ownership checks
