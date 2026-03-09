@props([
    'title' => 'Athenas Boutique',
])

<!DOCTYPE html>
<html lang="es" class="scroll-smooth antialiased">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $title }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance

        <style>
            .font-serif-display { font-family: 'Cormorant Garamond', serif; }
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="min-h-screen bg-stone-50 text-stone-800">
        {{ $slot }}
    </body>
</html>
