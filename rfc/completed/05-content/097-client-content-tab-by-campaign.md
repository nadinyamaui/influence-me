# 097 - Client Content Tab by Campaign Entity

**Labels:** `feature`, `content`, `clients`, `campaigns`, `ui`
**Depends on:** #096, #041

## Description

Render client linked content grouped by campaign entities rather than pivot campaign-name text.

## Implementation

### Grouping behavior
- Group by campaign record (`campaign.id`, display `campaign.name`).
- Include linked content from all supported platforms.
- Keep an "Uncategorized" group only for legacy/null-linked content if present.

### Display requirements
- Campaign header: campaign name, linked content count, aggregate reach.
- Media grid/cards remain compact.
- Each media card includes platform badge.
- Maintain aggregate stats banner for total linked metrics.

### Unlink behavior
- Unlink removes the relevant polymorphic `campaign_media` link row.

## Files to Modify
- `resources/views/pages/clients/show.blade.php`
- `app/Livewire/Clients/Show.php`

## Acceptance Criteria
- [ ] Content tab groups by campaign entity
- [ ] Mixed-platform content renders with platform context
- [ ] Aggregates are correct per campaign and global totals
- [ ] Unlink behavior is preserved
- [ ] Empty state is shown correctly
- [ ] Feature tests verify grouping and aggregation behavior
