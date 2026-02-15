# 098 - Proposal-Campaign Context Display

**Labels:** `feature`, `content`, `proposals`, `campaigns`, `ui`
**Depends on:** #093, #044, #045, #047

## Description

Show campaign collection context in proposal experiences when campaigns are linked through `campaigns.proposal_id`. A proposal may include multiple campaigns, each with scheduled content.

## Implementation

### Influencer proposal pages
- Create/edit pages must support linking or creating multiple campaigns for the proposal.
- Detail page must render all linked campaigns and their scheduled content.

### Client portal proposal pages
- Show full campaign collection context for client-visible proposals, including scheduled content per campaign.

### Behavior rules
- Campaign collection rendering is nullable-safe for legacy proposals with no linked campaigns.
- Campaign grouping source of truth is campaign entities linked via `campaigns.proposal_id`.
- Scheduled content display source of truth is scheduled posts linked to each campaign.

## Files to Modify
- `resources/views/pages/proposals/create.blade.php`
- `resources/views/pages/proposals/edit.blade.php`
- `resources/views/pages/proposals/show.blade.php`
- `resources/views/pages/portal/proposals/show.blade.php`

## Acceptance Criteria
- [ ] Influencer proposal pages show campaign collection context when campaigns are linked
- [ ] Client portal proposal detail shows campaign collection context when campaigns are linked
- [ ] Each campaign section includes scheduled content rows with media type and datetime
- [ ] Null/legacy no-campaign state is handled cleanly
- [ ] Feature tests cover multi-campaign and absent-campaign contexts
