@props([
    'title' => 'Influence Me',
    'bodyClass' => 'bg-white text-slate-600 selection:bg-indigo-100 selection:text-indigo-900',
])

<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
    <head>
        @include('partials.head')
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
