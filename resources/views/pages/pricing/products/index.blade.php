<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Pricing Products</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage reusable deliverables and package pricing by platform.</p>
        </div>

        <flux:button :href="route('pricing.products.create')" variant="primary" wire:navigate title="Add Product" aria-label="Add Product">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :label="__('Search')"
                :placeholder="__('Search by product name')"
            />

            <flux:select wire:model.live="status" :label="__('Status')">
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="platform" :label="__('Platform')">
                <option value="all">All Platforms</option>
                @foreach ($platformOptions as $platformOption)
                    <option value="{{ $platformOption->value }}">{{ $platformOption->label() }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="sort" :label="__('Sort')">
                @foreach ($sortOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
    </section>

    @if ($products->isEmpty())
        <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900/40">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">No pricing products found for the selected filters.</h2>
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Platform</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Media Type</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Billing Unit</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Price</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Status</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($products as $product)
                            <tr wire:key="catalog-product-{{ $product->id }}">
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $product->platform->label() }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $product->media_type?->label() ?? 'Generic' }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $product->billing_unit->label() }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $product->currency }} {{ number_format((float) $product->base_price, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if ($product->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Archived</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('pricing.products.edit', $product) }}"
                                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            title="Edit"
                                            aria-label="Edit"
                                            wire:navigate
                                        >
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>

                                        @if ($product->is_active)
                                            <button
                                                type="button"
                                                wire:click="archive({{ $product->id }})"
                                                wire:confirm="Archive '{{ $product->name }}'?"
                                                class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                                title="Archive"
                                                aria-label="Archive"
                                            >
                                                <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="unarchive({{ $product->id }})"
                                                wire:confirm="Unarchive '{{ $product->name }}'?"
                                                class="inline-flex items-center rounded-md border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-200 dark:hover:bg-emerald-950/40"
                                                title="Unarchive"
                                                aria-label="Unarchive"
                                            >
                                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                {{ $products->links() }}
            </div>
        </section>
    @endif
</div>
