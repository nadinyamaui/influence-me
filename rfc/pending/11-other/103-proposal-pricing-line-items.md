# 103 - Proposal Pricing Line Items (Snapshot Model)

**Labels:** `feature`, `proposals`, `pricing`
**Depends on:** #044, #045, #100, #101, #102

## Description

Extend proposal editing and preview to include commercial line items sourced from products, plans, or custom entries. Persist only snapshots on proposals so future catalog changes do not mutate proposal terms.

## Implementation

### Proposal Edit Flow
- Add Step 4: `Pricing` in `proposals.edit`.
- Allow adding line items from:
  - product
  - plan
  - custom
- Allow quantity and unit price overrides before save.

### Persistence
- Store all proposal commercial rows in `proposal_line_items`.
- Save snapshot columns (`name_snapshot`, platform/media snapshots, price snapshot, quantity, total).
- Recompute proposal commercial subtotal on save (computed in service, not inline UI).
- For cross-platform packages, persist explicit split rows per platform/media-type combination (no unresolved aggregate bucket quantity).

### Service Changes
- Add `ProposalPricingService` and integrate into `ProposalWorkflowService::updateDraftWithCampaignSchedule`.
- Pricing mutations only for editable statuses (`draft`, `revised`).

### Proposal Preview
- Update influencer preview page to show:
  - commercial line item table
  - subtotal/tax/total summary (tax optional per proposal if adopted)
  - source tag (`Product`, `Plan`, `Custom`)

## Files to Create
- `app/Services/Proposals/ProposalPricingService.php`
- proposal pricing value object/helper (if needed)

## Files to Modify
- `app/Livewire/Proposals/Edit.php`
- `resources/views/pages/proposals/edit.blade.php`
- `app/Livewire/Proposals/Show.php`
- `resources/views/pages/proposals/show.blade.php`
- `app/Services/Proposals/ProposalWorkflowService.php`

## Acceptance Criteria
- [ ] Proposal edit supports mixed-source pricing line items
- [ ] Cross-platform package quantities are persisted as explicit platform split rows
- [ ] Snapshots persist and remain unchanged after catalog edits
- [ ] Proposal preview renders commercial breakdown and totals
- [ ] Read-only behavior remains enforced for non-editable proposal statuses
- [ ] Feature tests cover create/edit/remove of proposal pricing items
