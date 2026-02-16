<x-mail::message>
# Proposal Approved

<p>{{ $clientName }} approved your proposal.</p>

<p><strong>Proposal:</strong> {{ $proposalTitle }}</p>

<x-mail::button :url="$proposalUrl">
View Proposal
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
