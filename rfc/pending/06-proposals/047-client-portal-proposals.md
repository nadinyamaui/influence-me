# 047 - Client Portal Proposals List and Detail

**Labels:** `feature`, `proposals`, `clients`, `ui`
**Depends on:** #035, #046

## Description

Create proposal pages in the client portal so clients can view proposals sent to them.

## Implementation

### Create Routes in `routes/portal.php`
```php
Route::livewire('portal/proposals', 'portal.proposals.index')
    ->middleware(['auth:client'])
    ->name('portal.proposals.index');

Route::livewire('portal/proposals/{proposal}', 'portal.proposals.show')
    ->middleware(['auth:client'])
    ->name('portal.proposals.show');
```

### Proposals List: `resources/views/pages/portal/proposals/index.blade.php`

**Table:**
| Column | Content |
|--------|---------|
| Title | Links to detail |
| Status | Badge |
| Received | sent_at date |
| Actions | View |

Filter by status. Only show proposals with status `Sent`, `Approved`, `Rejected`, or `Revised` (not `Draft`).
Implement filtering in the Livewire component query, not in Blade.

### Proposal Detail: `resources/views/pages/portal/proposals/show.blade.php`

- Proposal title
- Status badge
- Received date
- Rendered markdown content (same as influencer view)
- Action buttons: "Approve" and "Request Changes" (implemented in #048)

### Authorization
Scoped through the authenticated ClientUser's client:
```php
$proposals = auth('client')->user()->client->proposals()
    ->whereNot('status', ProposalStatus::Draft)
    ->latest('sent_at')
    ->paginate();
```

Verify proposal belongs to the client in the show method.

### Update Portal Sidebar
Update `href="#"` for "Proposals" to `route('portal.proposals.index')`.

## Files to Create
- `resources/views/pages/portal/proposals/index.blade.php`
- `resources/views/pages/portal/proposals/show.blade.php`

## Files to Modify
- `routes/portal.php` — add routes
- `resources/views/layouts/portal/sidebar.blade.php` — update proposals link

## Acceptance Criteria
- [ ] Proposals list shows only sent/approved/rejected/revised proposals
- [ ] Filter logic is implemented in the Livewire component query layer
- [ ] Proposal detail renders markdown correctly
- [ ] Data scoped to authenticated client
- [ ] Cannot view draft proposals
- [ ] Cannot view other clients' proposals
- [ ] Feature tests verify list, detail, and authorization
