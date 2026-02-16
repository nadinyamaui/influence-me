<x-mail::message>
# Hello,

{{ $influencerName }} has sent you a new proposal.

**Proposal title:** {{ $proposalTitle }}

**Preview:** {{ $proposalPreview }}

@if ($hasPortalAccess)
<x-mail::button :url="$portalUrl">
View Proposal
</x-mail::button>
@else
Your influencer has sent you a proposal.
@endif
</x-mail::message>
