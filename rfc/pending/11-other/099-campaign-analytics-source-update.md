# 099 - Campaign Analytics Source Update

**Labels:** `feature`, `analytics`, `campaigns`, `clients`
**Depends on:** #097, #064, #066

## Description

Update campaign analytics requirements so rollups use campaign entities from `campaigns` instead of pivot name strings.

## Implementation

### Analytics source of truth
- Campaign breakdown metrics must be keyed by campaign IDs/names from the campaigns table.
- Remove dependency on `pivot.campaign_name` grouping.

### Scoping
- Preserve influencer and client portal scoping rules.
- Ensure campaign metrics only include data from owned/scoped clients.

## Files to Modify
- `rfc/pending/08-analytics/064-campaign-client-analytics.md`
- `rfc/pending/08-analytics/066-client-portal-analytics.md`

## Acceptance Criteria
- [ ] Campaign rollups use campaign entity data
- [ ] No pivot-name-based campaign grouping remains in requirements
- [ ] Client-scoped analytics behavior is preserved
- [ ] Feature tests verify campaign grouping source and scoping
