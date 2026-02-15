# 095 - Client Campaigns Section (Dedicated UI)

**Labels:** `feature`, `content`, `clients`, `campaigns`, `ui`
**Depends on:** #093, #034

## Description

Add a dedicated Campaigns section on the client detail page for campaign lifecycle management.

## Implementation

### Client detail Campaigns section
- Display list of campaigns for the client.
- Show campaign name, optional linked proposal, created date, and media count.

### Campaign CRUD
- Create campaign (`name`, optional `description`, optional `proposal_id`).
- Edit campaign fields and proposal link.
- Delete campaign with confirmation.

### Proposal link behavior
- Proposal linking is optional.
- Proposal selection must be scoped to proposals that belong to the same client.
- Allow unlinking proposal from campaign.

### UI states
- Loading, empty, and validation error states are required.

## Files to Create/Modify
- `resources/views/pages/clients/show.blade.php` (Campaigns section)
- `app/Livewire/Clients/Show.php` (campaign actions)
- Any campaign form requests/components used by client detail page

## Acceptance Criteria
- [ ] Campaign CRUD is available from the client page
- [ ] Campaign actions are client-scoped and authorized
- [ ] Optional proposal link/unlink works
- [ ] Empty and loading states are implemented
- [ ] Feature tests cover CRUD, validation, and authorization
