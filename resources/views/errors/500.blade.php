<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>500 | {{ config('app.name') }}</title>
        <style>
            body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background: #f5f5f5; color: #18181b; }
            .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
            .card { width: 100%; max-width: 640px; background: #fff; border: 1px solid #e4e4e7; border-radius: 16px; padding: 40px; text-align: center; }
            .code { margin: 0; color: #71717a; font-size: 14px; letter-spacing: .08em; text-transform: uppercase; font-weight: 700; }
            h1 { margin: 12px 0 0; font-size: 32px; line-height: 1.2; }
            p { margin: 16px 0 0; color: #52525b; font-size: 16px; }
            a { display: inline-block; margin-top: 28px; padding: 12px 16px; border-radius: 10px; text-decoration: none; background: #18181b; color: #fff; font-weight: 600; }
        </style>
    </head>
    <body>
        <main class="wrap">
            <section class="card">
                <p class="code">500</p>
                <h1>Something went wrong</h1>
                <p>An unexpected error occurred while loading this page.</p>
                <a href="{{ route('home') }}">Return home</a>
            </section>
        </main>
    </body>
</html>
