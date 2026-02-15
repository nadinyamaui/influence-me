@props([
    'title' => 'Influence Me',
    'bodyClass' => 'bg-white text-slate-600 selection:bg-indigo-100 selection:text-indigo-900',
])

<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
            details > summary { list-style: none; }
            details > summary::-webkit-details-marker { display: none; }
        </style>
        {{ $head ?? '' }}
    </head>
    <body class="min-h-screen {{ $bodyClass }}">
        <x-marketing.header />

        {{ $slot }}

        <x-marketing.footer />
    </body>
</html>
