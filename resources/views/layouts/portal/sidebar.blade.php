@php
    use Illuminate\Support\Str;

    $clientUser = auth('client')->user();
    $clientName = $clientUser?->name ?? 'Client';
    $initials = Str::of($clientName)
        ->explode(' ')
        ->take(2)
        ->map(fn (string $word): string => Str::substr($word, 0, 1))
        ->implode('');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-2 font-semibold text-zinc-900 dark:text-zinc-100" wire:navigate>
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-900 text-xs font-semibold text-white dark:bg-zinc-100 dark:text-zinc-900">IM</span>
                    <span class="text-sm">Influence Me - Client Portal</span>
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Portal')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('portal.dashboard')" :current="request()->routeIs('portal.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="document-text" href="#">
                        {{ __('Proposals') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="banknotes" href="#">
                        {{ __('Invoices') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar" href="#">
                        {{ __('Analytics') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:dropdown position="bottom" align="start" class="hidden lg:block">
                <flux:sidebar.profile :name="$clientName" :initials="$initials" icon:trailing="chevrons-up-down" />

                <flux:menu>
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar :name="$clientName" :initials="$initials" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ $clientName }}</flux:heading>
                            <flux:text class="truncate">{{ $clientUser?->email }}</flux:text>
                        </div>
                    </div>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('portal.logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="portal-logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile :initials="$initials" icon-trailing="chevron-down" />

                <flux:menu>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="$clientName" :initials="$initials" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ $clientName }}</flux:heading>
                                <flux:text class="truncate">{{ $clientUser?->email }}</flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('portal.logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="portal-logout-button-mobile">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
