# 097 - Client Content Tab by Campaign Entity

**Labels:** `feature`, `content`, `clients`, `campaigns`, `ui`
**Depends on:** #096, #041

## Description

Render client linked content grouped by campaign entities rather than pivot campaign name text.

## Implementation

### Grouping behavior
- Group by campaign record (`campaign.id`, display `campaign.name`).
- Keep an "Uncategorized" group only for legacy/null-linked content if present.

### Display requirements
- Campaign header: campaign name, linked post count, aggregate reach.
- Media grid/cards remain compact.
- Maintain aggregate stats banner for total linked metrics.

### Unlink behavior
- Unlink continues to remove media association from campaign pivot.

## Files to Modify
- `resources/views/pages/clients/show.blade.php`
- `app/Livewire/Clients/Show.php`

## Acceptance Criteria
- [ ] Content tab groups by campaign entity
- [ ] Aggregates are correct per campaign and global totals
- [ ] Unlink behavior is preserved
- [ ] Empty state is shown correctly
- [ ] Feature tests verify grouping and aggregation behavior
