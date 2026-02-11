<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in with Facebook')" :description="__('Use Facebook to securely connect your Instagram influencer account.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($errors->has('oauth'))
            <div class="rounded-xl border border-red-300/70 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('oauth') }}
            </div>
        @endif

        <div class="flex flex-col gap-3">
            <flux:button :href="route('auth.facebook')" variant="primary" class="w-full" data-test="facebook-login-button">
                {{ __('Continue with Facebook') }}
            </flux:button>
            <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('This uses Meta Login for Instagram Graph access.') }}
            </p>
        </div>
    </div>
</x-layouts::auth>
