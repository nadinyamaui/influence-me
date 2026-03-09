@props([
    'title' => 'Athenas Boutique',
])

<!DOCTYPE html>
<html lang="es" class="scroll-smooth antialiased">
    <head>
        @include('partials.head')
        <style>
            body {
                font-family: 'Bricolage Grotesque', sans-serif;
            }

            .font-display {
                font-family: 'DM Serif Display', serif;
            }

            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f6efe8;
            }

            ::-webkit-scrollbar-thumb {
                background: #cab39d;
                border-radius: 999px;
            }
        </style>
        {{ $head ?? '' }}
    </head>
    <body class="min-h-screen bg-[#f7efe7] text-stone-900 selection:bg-amber-200 selection:text-stone-900">
        {{ $slot }}
    </body>
</html>
