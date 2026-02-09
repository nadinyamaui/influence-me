# 046 - Send Proposal to Client

**Labels:** `feature`, `proposals`
**Depends on:** #045

## Description

Implement the "Send to Client" action that changes a proposal's status to Sent and emails the client.

## Implementation

### Livewire Action
On the proposal detail page (#045), the "Send to Client" button triggers:

```php
public function send(): void
{
    $this->authorize('send', $this->proposal);

    $this->proposal->update([
        'status' => ProposalStatus::Sent,
        'sent_at' => now(),
    ]);

    // Send email notification
    Mail::to($this->proposal->client->email)
        ->send(new ProposalSent($this->proposal));

    session()->flash('success', 'Proposal sent to ' . $this->proposal->client->name);
}
```

### Confirmation
Show a confirmation before sending:
"Send this proposal to {client name} at {client email}?"

### Create Mailable
`App\Mail\ProposalSent`
- To: client email
- Subject: "New Proposal: {proposal title}"
- Content:
  - From: {influencer name}
  - Proposal title
  - Brief preview of content (first 200 chars)
  - "View Proposal" button linking to client portal (if portal access exists) or a simple "Your influencer has sent you a proposal" message
- Reply-to: influencer's email (if available)

### Validation
- Can only send if status is `Draft` or `Revised`
- Client must have an email address
- Show error if client has no email

## Files to Create
- `app/Mail/ProposalSent.php`
- `resources/views/mail/proposal-sent.blade.php`

## Files to Modify
- `resources/views/pages/proposals/show.blade.php` — wire up send button

## Acceptance Criteria
- [ ] "Send to Client" changes status to Sent
- [ ] `sent_at` timestamp recorded
- [ ] Email sent to client
- [ ] Confirmation shown before sending
- [ ] Cannot send if client has no email
- [ ] Cannot send if status is not Draft/Revised
- [ ] Feature tests cover sending and email dispatch
