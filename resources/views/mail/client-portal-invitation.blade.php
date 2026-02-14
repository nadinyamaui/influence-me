<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
        <p>Hello,</p>

        <p>{{ $influencerName }} invited you to the Influence Me client portal.</p>

        <p>
            Login URL: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
        </p>

        <p>
            Temporary Password: <strong>{{ $temporaryPassword }}</strong>
        </p>

        <p>Log in with this password and change it as soon as possible.</p>
    </body>
</html>
