# 036 - Invite Client to Portal

**Labels:** `feature`, `clients`
**Depends on:** #007, #019, #034

## Description

Add the ability for influencers to create portal access for a client. From the client detail page, clicking "Invite to Portal" creates a `ClientUser` account and sends a welcome email with a temporary password.

## Implementation

### Livewire Action on Client Detail Page
Add an "Invite to Portal" button (shown only if no `ClientUser` exists for this client).

**`inviteToPortal()` action:**
1. Validate the client has an email address (required for portal access)
2. Generate a random temporary password
3. Create `ClientUser`: name from client, email from client, hashed password
4. Send welcome email with login URL and temporary password
5. Show success flash message

### Create Mailable
`App\Mail\ClientPortalInvitation`
- To: client email
- Subject: "You've been invited to the Okacrm client portal"
- Content:
  - Welcome message with influencer's name
  - Portal login URL (`/portal/login`)
  - Temporary password
  - Instructions to log in and change password

### Revoke Access
Add "Revoke Portal Access" button (shown if `ClientUser` exists):
- Confirmation modal
- Deletes the `ClientUser` record
- Shows success message

### Client Detail Page Update
Show portal status:
- If `ClientUser` exists: "Portal access: Active" + Revoke button
- If no `ClientUser`: "No portal access" + Invite button
- If client has no email: "Add an email to enable portal access"

## Files to Create
- `app/Mail/ClientPortalInvitation.php`
- `resources/views/mail/client-portal-invitation.blade.php`

## Files to Modify
- `resources/views/pages/clients/show.blade.php` — add invite/revoke UI

## Acceptance Criteria
- [ ] "Invite to Portal" creates ClientUser and sends email
- [ ] Cannot invite if client has no email
- [ ] Welcome email contains login URL and temp password
- [ ] "Revoke Portal Access" deletes ClientUser
- [ ] Portal status shown correctly on client detail
- [ ] Feature tests cover invite, revoke, and email sending
