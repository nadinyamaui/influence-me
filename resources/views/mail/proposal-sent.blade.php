<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
        <p>Hello,</p>

        <p>{{ $influencerName }} has sent you a new proposal.</p>

        <p>
            <strong>Proposal title:</strong> {{ $proposalTitle }}
        </p>

        <p>
            <strong>Preview:</strong> {{ $proposalPreview }}
        </p>

        @if ($hasPortalAccess)
            <p>
                <a
                    href="{{ $portalUrl }}"
                    style="display: inline-block; border-radius: 6px; background: #1d4ed8; color: #ffffff; text-decoration: none; padding: 10px 16px;"
                >
                    View Proposal
                </a>
            </p>
        @else
            <p>Your influencer has sent you a proposal.</p>
        @endif
    </body>
</html>
