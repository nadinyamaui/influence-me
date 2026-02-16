<x-mail::message>
# Revision Requested

<p>{{ $clientName }} requested changes to your proposal.</p>

<p><strong>Proposal:</strong> {{ $proposalTitle }}</p>

<p><strong>Requested Changes:</strong></p>
<p>{{ $revisionNotes }}</p>

<x-mail::button :url="$editProposalUrl">
Edit Proposal
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
