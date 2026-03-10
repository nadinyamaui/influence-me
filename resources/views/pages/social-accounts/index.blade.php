@php
    use App\Enums\SyncStatus;
    use Illuminate\Support\Str;

    $providerLabel = $providerNetwork->label();
    $providerValue = $providerNetwork->value;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $providerLabel }} Accounts</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">View all connected {{ $providerLabel }} accounts and their sync health.</p>
        </div>

        @if ($accounts->isNotEmpty())
                <a
                href="{{ route('social.add', ['provider' => $providerValue]) }}"
                class="inline-flex min-h-11 items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
            >
                Connect Another Account
            </a>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('oauth') || $errors->has('disconnect') || $errors->has('sync'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/50 dark:text-rose-200">
            {{ $errors->first('oauth') ?? $errors->first('disconnect') ?? $errors->first('sync') }}
        </div>
    @endif

    @if ($accounts->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No {{ $providerLabel }} accounts connected.</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Click below to connect your first account.</p>
            <a
                href="{{ route('social.add', ['provider' => $providerValue]) }}"
                class="mt-5 inline-flex min-h-11 items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
            >
                Connect {{ $providerLabel }} Account
            </a>
        </section>
    @else
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($accounts as $account)
                @php
                    $tokenExpired = $account->token_expires_at?->isPast() ?? false;
                    $tokenExpiringSoon = ! $tokenExpired && ($account->token_expires_at?->lte(now()->addDays(7)) ?? false);
                    $syncStatusValue = $account->sync_status?->value ?? SyncStatus::Idle->value;
                    $accountIsSyncing = $syncStatusValue === SyncStatus::Syncing->value;
                @endphp

                <article
                    wire:key="social-account-{{ $account->id }}"
                    @if ($accountIsSyncing)
                        wire:poll.5s
                    @endif
                    class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div class="flex items-start gap-3">
                        @if ($account->profile_picture_url)
                            <img
                                src="{{ $account->profile_picture_url }}"
                                alt="{{ $account->username }} profile picture"
                                class="h-12 w-12 rounded-full object-cover"
                            >
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-200 text-sm font-semibold uppercase text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                                {{ Str::of($account->username)->substr(0, 2) }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ '@'.$account->username }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                @if ($account->is_primary)
                                    <span class="rounded-full bg-sky-100 px-2 py-1 font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">Primary</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60">
                            <dt class="text-zinc-500 dark:text-zinc-300">Followers</dt>
                            <dd class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($account->followers_count ?? 0) }}</dd>
                        </div>
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60">
                            <dt class="text-zinc-500 dark:text-zinc-300">Media</dt>
                            <dd class="mt-1 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($account->media_count ?? 0) }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-zinc-500 dark:text-zinc-300">Sync status</span>
                            @if ($accountIsSyncing)
                                <span class="inline-flex items-center gap-2 font-medium text-amber-600 dark:text-amber-300">
                                    <span class="h-3 w-3 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></span>
                                    Syncing...
                                </span>
                            @elseif ($syncStatusValue === SyncStatus::Failed->value)
                                <span class="inline-flex items-center gap-2 font-medium text-rose-600 dark:text-rose-300">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                    Sync failed
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 font-medium text-emerald-600 dark:text-emerald-300">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    Up to date
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-zinc-500 dark:text-zinc-300">Last synced</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $account->last_synced_at?->diffForHumans() ?? 'Never' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-zinc-500 dark:text-zinc-300">Token status</span>
                            @if ($tokenExpired)
                                <span class="font-medium text-rose-600 dark:text-rose-300">
                                    Expired
                                    <a
                                        href="{{ route('social.add', ['provider' => $providerValue]) }}"
                                        class="underline underline-offset-2 hover:text-rose-700 dark:hover:text-rose-200"
                                    >
                                        Re-authenticate
                                    </a>
                                </span>
                            @elseif ($tokenExpiringSoon)
                                <span class="font-medium text-amber-600 dark:text-amber-300">
                                    Expires within 7 days
                                    <a
                                        href="{{ route('social.add', ['provider' => $providerValue]) }}"
                                        class="underline underline-offset-2 hover:text-amber-700 dark:hover:text-amber-200"
                                    >
                                        Re-authenticate
                                    </a>
                                </span>
                            @else
                                <span class="font-medium text-emerald-600 dark:text-emerald-300">Active</span>
                            @endif
                        </div>
                    </div>

                    @if ($syncStatusValue === SyncStatus::Failed->value && $account->last_sync_error)
                        <details class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm dark:border-rose-900/40 dark:bg-rose-950/30">
                            <summary class="cursor-pointer font-medium text-rose-700 dark:text-rose-200">View sync error</summary>
                            <p class="mt-2 whitespace-pre-wrap text-rose-700 dark:text-rose-200">{{ $account->last_sync_error }}</p>
                        </details>
                    @endif

                    <div class="mt-5 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            wire:click="syncNow({{ $account->id }})"
                            @disabled($accountIsSyncing)
                            class="inline-flex min-h-11 items-center rounded-md border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 transition hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-amber-700 dark:text-amber-200 dark:hover:bg-amber-900/20"
                        >
                            Sync Now
                        </button>

                        @if (! $account->is_primary)
                            <button
                                type="button"
                                wire:click="setPrimary({{ $account->id }})"
                                class="inline-flex min-h-11 items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                            >
                                Set Primary
                            </button>
                        @endif

                        <button
                            type="button"
                            wire:click="disconnect({{ $account->id }})"
                            wire:confirm="Disconnect @{{ $account->username }}?"
                            class="inline-flex min-h-11 items-center rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-800 dark:text-rose-200 dark:hover:bg-rose-950/40"
                        >
                            Disconnect
                        </button>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

</div>
