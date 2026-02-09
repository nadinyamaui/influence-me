# 044 - Proposal Create and Edit Pages

**Labels:** `feature`, `proposals`, `ui`
**Depends on:** #008, #012

## Description

Create Livewire pages for creating and editing proposals with a markdown content editor.

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

### Create Page: `resources/views/pages/proposals/create.blade.php`

**Form Fields:**
- Title: `<flux:input>` (required)
- Client: `<flux:select>` with user's clients
- Content: `<flux:textarea>` with large rows (10+) for markdown writing
  - Helper text: "Supports Markdown formatting"
- Preview toggle: button that switches between edit and preview mode
- In preview mode: render content with `Str::markdown()` in a styled container
- Cancel button (back to proposals list)
- Save as Draft button

### Edit Page: `resources/views/pages/proposals/edit.blade.php`
- Same form, pre-filled with existing data
- Only editable when status is Draft or Revised
- If status is Sent/Approved/Rejected: show read-only view with "Duplicate" option
- Delete button with confirmation

### Livewire Logic
```php
// Create
public function save(): void
{
    $validated = $this->validate(/* rules */);
    auth()->user()->proposals()->create([
        ...$validated,
        'status' => ProposalStatus::Draft,
    ]);
    redirect()->route('proposals.index')->with('success', 'Proposal created.');
}

// Edit
public function update(): void
{
    $this->authorize('update', $this->proposal);
    $validated = $this->validate(/* rules */);
    $this->proposal->update($validated);
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
- [ ] Only Draft/Revised proposals are editable
- [ ] Client dropdown scoped to user's clients
- [ ] Delete with confirmation works
- [ ] Authorization enforced
- [ ] Feature tests cover create, edit, and validation
