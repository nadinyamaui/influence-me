# 048 - Proposal Approval and Revision Workflow

**Labels:** `feature`, `proposals`
**Depends on:** #047, #098

## Description

Implement the client-side actions on proposals: Approve and Request Changes. These actions update the proposal status and notify the influencer.

## Implementation

### Client Portal Actions
On the proposal detail page in the portal (#047), add action buttons (only shown when status is `Sent`):

**Approve:**
```php
public function approve(): void
{
    $this->proposal->update([
        'status' => ProposalStatus::Approved,
        'responded_at' => now(),
    ]);

    Mail::to($this->proposal->user->email)
        ->send(new ProposalApproved($this->proposal));

    session()->flash('success', 'Proposal approved!');
}
```

**Request Changes:**
- Opens a modal with a textarea for revision notes
- On submit:
```php
public function requestChanges(string $notes): void
{
    $this->proposal->update([
        'status' => ProposalStatus::Revised,
        'revision_notes' => $notes,
        'responded_at' => now(),
    ]);

    Mail::to($this->proposal->user->email)
        ->send(new ProposalRevisionRequested($this->proposal));

    session()->flash('success', 'Revision request sent.');
}
```

### Validation
- Actions only available when status is `Sent`
- Revision notes required when requesting changes (min: 10 chars)
- Cannot approve/revise if already responded
- Approval/revision acts on the full proposal package, including all linked campaigns and scheduled content

### Create Mailables

**`App\Mail\ProposalApproved`:**
- To: influencer email
- Subject: "Proposal Approved: {title}"
- Content: Client approved the proposal, with link to view it

**`App\Mail\ProposalRevisionRequested`:**
- To: influencer email
- Subject: "Revision Requested: {title}"
- Content: Client requested changes, revision notes included, link to edit

## Files to Create
- `app/Mail/ProposalApproved.php`
- `app/Mail/ProposalRevisionRequested.php`
- `resources/views/mail/proposal-approved.blade.php`
- `resources/views/mail/proposal-revision-requested.blade.php`

## Files to Modify
- `resources/views/pages/portal/proposals/show.blade.php` — add action buttons and revision modal

## Acceptance Criteria
- [ ] "Approve" changes status to Approved and records timestamp
- [ ] "Request Changes" opens modal for revision notes
- [ ] Revision notes stored on proposal
- [ ] Emails sent to influencer for both actions
- [ ] Actions only available when status is Sent
- [ ] Cannot act on already-responded proposals
- [ ] Approval/revision is exercised against proposals that include multiple campaigns and scheduled content
- [ ] Feature tests cover approve, request changes, and email dispatch
