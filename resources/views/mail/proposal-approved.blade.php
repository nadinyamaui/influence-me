<x-mail::message>
# Proposal Approved

{{ $clientName }} approved your proposal.

**Proposal title:** {{ $proposalTitle }}

<x-mail::button :url="$proposalUrl">
View Proposal
</x-mail::button>
</x-mail::message>
