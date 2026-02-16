# 104 - Proposal Send Commercial Validation

**Labels:** `feature`, `proposals`, `validation`
**Depends on:** #046, #103

## Description

Require commercial readiness before a proposal can be sent to a client.

## Implementation

### Send Validation Rules
Extend `ProposalWorkflowService::assertSendable()`:
- Proposal must include at least one `proposal_line_item`.
- Each line item must have:
  - quantity > 0
  - unit_price >= 0
  - line_total consistent with quantity * unit_price
- All line items must belong to the same proposal scope.

Keep existing send guards unchanged:
- status must be `draft` or `revised`
- client email required
- campaign/scheduled content validations remain active

### Error UX
- Surface actionable send validation errors on proposal preview.

## Files to Modify
- `app/Services/Proposals/ProposalWorkflowService.php`
- `app/Livewire/Proposals/Show.php`
- `resources/views/pages/proposals/show.blade.php`
- `tests/Feature/Proposals/ProposalSendWorkflowTest.php`

## Acceptance Criteria
- [ ] Send is blocked when proposal has no commercial line items
- [ ] Send is blocked when commercial line items contain invalid values
- [ ] Existing campaign scheduling send rules still pass/fail as before
- [ ] Validation errors render without mutating proposal status
- [ ] Feature tests cover positive and negative commercial validation paths
