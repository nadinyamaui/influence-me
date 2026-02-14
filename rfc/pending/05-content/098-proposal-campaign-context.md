# 098 - Proposal-Campaign Context Display

**Labels:** `feature`, `content`, `proposals`, `campaigns`, `ui`
**Depends on:** #093, #044, #045, #047

## Description

Show campaign context in proposal experiences when a campaign is linked through `campaigns.proposal_id`.

## Implementation

### Influencer proposal pages
- Show linked campaign name and client context on create/edit/detail pages when present.
- Do not require a campaign link to create or edit proposals.

### Client portal proposal pages
- Show linked campaign context for client-visible proposals when present.

### Behavior rules
- Campaign context is optional and nullable-safe.
- No direct proposal-side campaign selection is required by this RFC.

## Files to Modify
- `resources/views/pages/proposals/create.blade.php`
- `resources/views/pages/proposals/edit.blade.php`
- `resources/views/pages/proposals/show.blade.php`
- `resources/views/pages/portal/proposals/show.blade.php`

## Acceptance Criteria
- [ ] Influencer proposal pages show optional campaign context
- [ ] Client portal proposal detail shows optional campaign context
- [ ] Null campaign state is handled cleanly
- [ ] Feature tests cover present and absent campaign context
