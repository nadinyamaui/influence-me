<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Pricing Plans</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Compose reusable bundles from your active pricing products.</p>
        </div>

        <flux:button :href="route('pricing.plans.create')" variant="primary" wire:navigate title="Add Plan" aria-label="Add Plan">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Search plans by name or description')"
            />

            <flux:select wire:model.live="status" :label="__('Status')">
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="sort" :label="__('Sort')">
                @foreach ($sortOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($plans->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No pricing plans found for the selected filters.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Items</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Bundle Price</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($plans as $plan)
                            <tr wire:key="catalog-plan-{{ $plan->id }}">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $plan->name }}</p>
                                    @if ($plan->description)
                                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $plan->items_count }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">
                                    @if ($plan->bundle_price !== null)
                                        {{ $plan->currency }} {{ number_format((float) $plan->bundle_price, 2) }}
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">Not set</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($plan->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Archived</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('pricing.plans.edit', $plan) }}"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            title="Edit"
                                            aria-label="Edit"
                                            wire:navigate
                                        >
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $plans->links() }}
            </div>
        </section>
    @endif
</div>
