# 045 - Proposal Preview/Detail Page

**Labels:** `feature`, `proposals`, `ui`
**Depends on:** #044

## Description

Create a Livewire page at `/proposals/{proposal}` showing the rendered markdown proposal as the client will see it. This is the influencer's view of the proposal.

## Implementation

### Create Route
```php
Route::livewire('proposals/{proposal}', 'proposals.show')
    ->middleware(['auth'])
    ->name('proposals.show');
```

### Create Livewire Page
`resources/views/pages/proposals/show.blade.php`

### Page Content

**Header:**
- Proposal title (large)
- Status badge
- Client name
- Created/updated dates

**Action Buttons (conditional):**
- Draft: "Edit" + "Send to Client"
- Sent: "Waiting for response..." (no actions)
- Approved: "Approved" badge
- Rejected: "Rejected" badge
- Revised: "Edit" (to revise) + "Send Again"

**Proposal Content:**
- Rendered markdown using `Str::markdown($proposal->content)`
- Styled container with proper typography (prose class from Tailwind)

**Revision Notes (if status is Revised):**
- Highlighted section showing client's revision notes
- "The client requested changes:" + notes text

**Meta Footer:**
- Sent at: {date} (if sent)
- Responded at: {date} (if responded)

## Files to Create
- `resources/views/pages/proposals/show.blade.php`

## Files to Modify
- `routes/web.php` — add route

## Acceptance Criteria
- [ ] Page renders at `/proposals/{proposal}`
- [ ] Markdown content rendered correctly with prose styling
- [ ] Status-appropriate action buttons shown
- [ ] Revision notes displayed when status is Revised
- [ ] Authorization enforced (only owning user)
- [ ] Feature test verifies display for each status

## Campaign Context Note

Optional campaign context display for proposal detail pages is defined in RFC `098`.
