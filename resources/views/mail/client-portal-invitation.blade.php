<x-mail::message>
# Hello,

{{ $influencerName }} invited you to the Influence Me client portal.

<x-mail::panel>
Login URL: {{ $loginUrl }}

Temporary Password: **{{ $temporaryPassword }}**
</x-mail::panel>

Log in with this password and change it as soon as possible.

<x-mail::button :url="$loginUrl">
Go to Client Portal
</x-mail::button>
</x-mail::message>
