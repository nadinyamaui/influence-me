# 106 - Invoice and Portal Commercial Context Display

**Labels:** `feature`, `invoicing`, `clients`, `ui`
**Depends on:** #051, #056, #105

## Description

Expose commercial source context in influencer and client portal invoice views so line items clearly indicate whether they came from a product, plan, custom entry, or proposal snapshot.

## Implementation

### Influencer Invoice Detail
- Show source badge per line item (`Product`, `Plan`, `Custom`, `Proposal Snapshot`).
- Show proposal reference when line item originated from proposal import.

### Client Portal Invoice Detail
- Render same line-item commercial context in read-only mode.
- No edit/delete actions for portal users.

### Authorization
- Preserve strict client scoping via `client` guard.

## Files to Modify
- influencer invoice show component and Blade (RFC #051 files)
- portal invoice show component and Blade (RFC #056 files)

## Acceptance Criteria
- [ ] Influencer invoice detail shows line item commercial source context
- [ ] Portal invoice detail shows same commercial context read-only
- [ ] Portal authorization and scoping remain correct
- [ ] Feature tests cover display and cross-client access denial
