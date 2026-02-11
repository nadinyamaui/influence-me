<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Client Portal')" :description="__('Log in to view your campaigns, proposals, and invoices')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('portal.login.store') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Log in') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
