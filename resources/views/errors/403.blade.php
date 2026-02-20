<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-neutral-950">
        <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center justify-center px-6 py-16">
            <div class="w-full rounded-2xl border border-zinc-200 bg-white p-10 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm font-semibold uppercase tracking-widest text-zinc-500">403</p>
                <h1 class="mt-3 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">Access denied</h1>
                <p class="mt-4 text-base text-zinc-600 dark:text-zinc-300">You don't have permission to access this page.</p>
                <div class="mt-8 flex justify-center">
                    <flux:button href="{{ route('dashboard') }}" wire:navigate variant="primary">Return to dashboard</flux:button>
                </div>
            </div>
        </main>
        @fluxScripts
    </body>
</html>
