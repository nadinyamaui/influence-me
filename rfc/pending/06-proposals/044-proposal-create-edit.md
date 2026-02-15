# 044 - Proposal Create and Edit Pages

**Labels:** `feature`, `proposals`, `ui`
**Depends on:** #008, #010, #012, #093

## Description

Create Livewire pages for creating and editing proposals with a markdown content editor, campaign planning, and campaign-level scheduled content entries.

## Implementation

### Create Routes
```php
Route::livewire('proposals/create', 'proposals.create')
    ->middleware(['auth'])
    ->name('proposals.create');

Route::livewire('proposals/{proposal}/edit', 'proposals.edit')
    ->middleware(['auth'])
    ->name('proposals.edit');
```

### Create Form Request
`App\Http\Requests\StoreProposalRequest`:
- `title`: required, string, max:255
- `client_id`: required, exists:clients,id (scoped to user)
- `content`: required, string, max:50000
- `campaigns`: required, array, min:1
- `campaigns.*.id`: nullable, exists:campaigns,id (scoped to same client/user when present)
- `campaigns.*.name`: required_without:campaigns.*.id, string, max:255
- `campaigns.*.description`: nullable, string, max:5000
- `campaigns.*.scheduled_items`: required, array, min:1
- `campaigns.*.scheduled_items.*.title`: required, string, max:255
- `campaigns.*.scheduled_items.*.description`: nullable, string, max:5000
- `campaigns.*.scheduled_items.*.media_type`: required, in:post,reel,story
- `campaigns.*.scheduled_items.*.instagram_account_id`: required, exists:instagram_accounts,id (scoped to user)
- `campaigns.*.scheduled_items.*.scheduled_at`: required, date

### Create Page: `resources/views/pages/proposals/create.blade.php`

**Form Fields:**
- Title: `<flux:input>` (required)
- Client: `<flux:select>` with user's clients
- Content: `<flux:textarea>` with large rows (10+) for markdown writing
  - Helper text: "Supports Markdown formatting"
- Campaign builder (required, min 1):
  - Add/remove campaign sections
  - Campaign name and optional description
  - Optional selection of existing client campaign
- Scheduled content builder inside each campaign (required, min 1 per campaign):
  - Title
  - Content type (`Post`, `Reel`, `Story`)
  - Instagram account
  - Date/time
  - Optional description
- Preview toggle: button that switches between edit and preview mode
- In preview mode: render content with `Str::markdown()` in a styled container
- Cancel button (back to proposals list)
- Save as Draft button

### Edit Page: `resources/views/pages/proposals/edit.blade.php`
- Same form, pre-filled with existing data
- Includes linked campaigns and per-campaign scheduled content items
- Only editable when status is Draft or Revised
- If status is Sent/Approved/Rejected: show read-only view with "Duplicate" option
- Delete button with confirmation

### Livewire Logic
```php
// Create
public function save(): void
{
    $validated = $this->validate(/* rules */);
    ProposalWorkflowService::createDraftWithCampaignSchedule(auth()->user(), $validated);
    redirect()->route('proposals.index')->with('success', 'Proposal created.');
}

// Edit
public function update(): void
{
    $this->authorize('update', $this->proposal);
    $validated = $this->validate(/* rules */);
    ProposalWorkflowService::updateDraftWithCampaignSchedule($this->proposal, $validated);
    redirect()->route('proposals.show', $this->proposal)->with('success', 'Proposal updated.');
}
```

## Files to Create
- `resources/views/pages/proposals/create.blade.php`
- `resources/views/pages/proposals/edit.blade.php`
- `app/Http/Requests/StoreProposalRequest.php`

## Files to Modify
- `routes/web.php` — add routes

## Acceptance Criteria
- [ ] Create form renders and saves a draft proposal
- [ ] Edit form loads existing data
- [ ] Markdown preview toggle works
- [ ] Proposal requires at least one linked campaign
- [ ] Every linked campaign requires at least one scheduled content item
- [ ] Scheduled content enforces valid `media_type` (`post`, `reel`, `story`)
- [ ] Campaign and schedule ownership scoping is enforced for authenticated influencer
- [ ] Only Draft/Revised proposals are editable
- [ ] Client dropdown scoped to user's clients
- [ ] Delete with confirmation works
- [ ] Authorization enforced
- [ ] Feature tests cover create, edit, and validation
