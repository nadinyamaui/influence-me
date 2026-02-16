<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
        <p>Hello,</p>

        <p>{{ $clientName }} requested changes to your proposal.</p>

        <p>
            <strong>Proposal:</strong> {{ $proposalTitle }}
        </p>

        <p>
            <strong>Requested Changes:</strong>
        </p>

        <p>{{ $revisionNotes }}</p>

        <p>
            <a
                href="{{ $editProposalUrl }}"
                style="display: inline-block; border-radius: 6px; background: #d97706; color: #ffffff; text-decoration: none; padding: 10px 16px;"
            >
                Edit Proposal
            </a>
        </p>
    </body>
</html>
