<x-mail::message>
# Revision Requested

{{ $clientName }} requested changes to your proposal.

**Proposal title:** {{ $proposalTitle }}

**Revision notes:**

{{ $revisionNotes }}

<x-mail::button :url="$editUrl">
Edit Proposal
</x-mail::button>
</x-mail::message>
