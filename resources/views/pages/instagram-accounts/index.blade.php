@php
    use App\Enums\SyncStatus;
    use Illuminate\Support\Str;
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Instagram Accounts</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">View all connected Instagram accounts and their sync health.</p>
    </div>

    @if ($accounts->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No Instagram accounts connected.</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Click below to connect your first account.</p>
            <a
                href="#"
                class="mt-5 inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
            >
                Connect Instagram Account
            </a>
        </section>
    @else
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($accounts as $account)
                @php
                    $tokenExpired = $account->token_expires_at?->isPast() ?? false;
                    $tokenExpiringSoon = ! $tokenExpired && ($account->token_expires_at?->lte(now()->addDays(7)) ?? false);
                    $accountTypeLabel = $account->account_type?->value ? ucfirst($account->account_type->value) : 'Unknown';
                    $syncStatusValue = $account->sync_status?->value ?? SyncStatus::Idle->value;
                @endphp

                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
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
                                <span class="rounded-full bg-zinc-100 px-2 py-1 font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ $accountTypeLabel }}</span>
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
                            @if ($syncStatusValue === SyncStatus::Syncing->value)
                                <span class="inline-flex items-center gap-2 font-medium text-amber-600 dark:text-amber-300">
                                    <span class="h-3 w-3 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></span>
                                    Syncing
                                </span>
                            @elseif ($syncStatusValue === SyncStatus::Failed->value)
                                <span class="inline-flex items-center gap-2 font-medium text-rose-600 dark:text-rose-300">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                    Failed
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 font-medium text-emerald-600 dark:text-emerald-300">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    Idle
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
                                <span class="font-medium text-rose-600 dark:text-rose-300">Expired</span>
                            @elseif ($tokenExpiringSoon)
                                <span class="font-medium text-amber-600 dark:text-amber-300">Expires within 7 days</span>
                            @else
                                <span class="font-medium text-emerald-600 dark:text-emerald-300">Active</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>
