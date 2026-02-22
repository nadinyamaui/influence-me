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
                <flux:navbar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item icon="photo" href="#">
                    {{ __('Content') }}
                </flux:navbar.item>
                <flux:navbar.item icon="chart-bar" :href="route('analytics.index')" :current="request()->routeIs('analytics.index')" wire:navigate>
                    {{ __('Analytics') }}
                </flux:navbar.item>
                <flux:navbar.item icon="users" href="#">
                    {{ __('Clients') }}
                </flux:navbar.item>
                <flux:navbar.item icon="document-text" :href="route('proposals.index')" :current="request()->routeIs('proposals.*')" wire:navigate>
                    {{ __('Proposals') }}
                </flux:navbar.item>
                <flux:navbar.item icon="banknotes" href="#">
                    {{ __('Invoices') }}
                </flux:navbar.item>
                <flux:navbar.item icon="tag" :href="route('pricing.products.index')" :current="request()->routeIs('pricing.products.*')" wire:navigate>
                    {{ __('Pricing') }}
                </flux:navbar.item>
                <flux:navbar.item icon="at-symbol" :href="route('instagram-accounts.index')" :current="request()->routeIs('instagram-accounts.index')" wire:navigate>
                    {{ __('Accounts') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="photo" href="#">
                        {{ __('Content') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('analytics.index')" :current="request()->routeIs('analytics.index')" wire:navigate>
                        {{ __('Analytics') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Manage')">
                    <flux:sidebar.item icon="users" href="#">
                        {{ __('Clients') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('proposals.index')" :current="request()->routeIs('proposals.*')" wire:navigate>
                        {{ __('Proposals') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" href="#">
                        {{ __('Invoices') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Settings')">
                    <flux:sidebar.item icon="tag" :href="route('pricing.products.index')" :current="request()->routeIs('pricing.products.*')" wire:navigate>
                        {{ __('Pricing Products') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="squares-2x2" :href="route('pricing.plans.index')" :current="request()->routeIs('pricing.plans.*')" wire:navigate>
                        {{ __('Pricing Plans') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="receipt-percent" :href="route('pricing.tax-rates.index')" :current="request()->routeIs('pricing.tax-rates.*')" wire:navigate>
                        {{ __('Tax Rates') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Instagram')">
                    <flux:sidebar.item icon="at-symbol" :href="route('instagram-accounts.index')" :current="request()->routeIs('instagram-accounts.index')" wire:navigate>
                        {{ __('Accounts') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
