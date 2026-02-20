# 096 - Link Content to Campaign (Single + Batch)

**Labels:** `feature`, `content`, `campaigns`, `ui`
**Depends on:** #094, #095, #038, #039

## Description

Update content-linking flows to target campaigns via `campaign_id` and store links through polymorphic `campaign_media` rows.

## Implementation

### Single link flow
From content detail modal:
1. Select client.
2. Select campaign scoped to selected client.
3. Select platform-aware content target (or use current selected item when opened from detail).
4. Optional notes field.
5. Save link to `campaign_media` using `campaign_id`, `platform`, `linkable_type`, `linkable_id`.

### Batch link flow
From content gallery batch mode:
1. Select media items.
2. Open link modal.
3. Select client and campaign.
4. Attach all selected items to chosen campaign through polymorphic link rows.

### Inline campaign creation
- Provide optional "Create campaign" action inside link flow.
- Inline create must enforce client ownership and campaign validation.

### Validation/authorization
- `campaign_id` required when linking.
- Campaign must belong to selected client and authenticated influencer.
- `linkable_type` and `linkable_id` must resolve to influencer-owned media for selected `platform`.

## Files to Modify
- `resources/views/pages/content/index.blade.php`
- `app/Livewire/Content/Index.php`
- `app/Services/Content/ContentClientLinkService.php`

## Acceptance Criteria
- [ ] Single link writes campaign-scoped polymorphic row
- [ ] Batch link writes campaign-scoped polymorphic rows
- [ ] Inline campaign creation works in link flow
- [ ] Validation and authorization enforced
- [ ] Feature tests cover single, batch, inline-create, and authorization paths
