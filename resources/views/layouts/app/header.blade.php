<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            @guest
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-zinc-900 underline-offset-4 hover:underline dark:text-zinc-50">
                        {{ __('Log In') }}
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="rounded-full bg-zinc-900 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-white dark:text-zinc-900"
                    >
                        {{ __('Get Started') }}
                    </a>
                </div>
            @else
                <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                    <flux:tooltip :content="__('Search')" position="bottom">
                        <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                    </flux:tooltip>
                    <flux:tooltip :content="__('Repository')" position="bottom">
                        <flux:navbar.item
                            class="h-10 max-lg:hidden [&>div>svg]:size-5"
                            icon="folder-git-2"
                            href="https://github.com/laravel/livewire-starter-kit"
                            target="_blank"
                            :label="__('Repository')"
                        />
                    </flux:tooltip>
                    <flux:tooltip :content="__('Documentation')" position="bottom">
                        <flux:navbar.item
                            class="h-10 max-lg:hidden [&>div>svg]:size-5"
                            icon="book-open-text"
                            href="https://laravel.com/docs/starter-kits#livewire"
                            target="_blank"
                            :label="__('Documentation')"
                        />
                    </flux:tooltip>
                </flux:navbar>

                <x-desktop-user-menu />
            @endguest
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
