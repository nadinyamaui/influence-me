<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
        <p>Hello,</p>

        <p>{{ $clientName }} approved your proposal.</p>

        <p>
            <strong>Proposal:</strong> {{ $proposalTitle }}
        </p>

        <p>
            <a
                href="{{ $proposalUrl }}"
                style="display: inline-block; border-radius: 6px; background: #059669; color: #ffffff; text-decoration: none; padding: 10px 16px;"
            >
                View Proposal
            </a>
        </p>
    </body>
</html>
