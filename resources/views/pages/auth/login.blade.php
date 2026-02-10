<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Log in with Instagram')"
            :description="__('Use your Instagram Professional account to continue.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($errors->has('instagram'))
            <flux:text class="text-center text-red-600">{{ $errors->first('instagram') }}</flux:text>
        @endif

        <flux:button
            variant="primary"
            :href="route('auth.instagram')"
            icon="camera"
            class="w-full"
            data-test="instagram-login-button"
        >
            {{ __('Login with Instagram') }}
        </flux:button>
    </div>
</x-layouts::auth>
