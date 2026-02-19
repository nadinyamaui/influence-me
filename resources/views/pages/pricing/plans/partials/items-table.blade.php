@php
    $productsById = $products->keyBy('id');
    $itemsDescription = $itemsDescription ?? 'Adjust products, quantities, and optional unit overrides.';
    $showProductsEmptyMessage = $showProductsEmptyMessage ?? false;
    $productsEmptyMessage = $productsEmptyMessage ?? 'Add at least one active pricing product before composing a plan.';
@endphp

<div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Plan Items</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $itemsDescription }}</p>
        </div>

        <flux:button type="button" variant="ghost" wire:click="addItemRow" title="Add Row" aria-label="Add Row">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </flux:button>
    </div>

    @if ($showProductsEmptyMessage && $products->isEmpty())
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
            {{ $productsEmptyMessage }}
        </div>
    @endif

    <flux:error name="items" />

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Product</th>
                    <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Qty</th>
                    <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Unit Override</th>
                    <th class="px-3 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">Row Total</th>
                    <th class="px-3 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($items as $index => $item)
                    @php
                        $selectedProduct = $productsById->get((int) ($item['catalog_product_id'] ?: 0));
                        $quantity = is_numeric($item['quantity'] ?? null) ? (float) $item['quantity'] : 0;
                        $override = $item['unit_price_override'] ?? '';
                        $unitPrice = $override !== '' && is_numeric($override)
                            ? (float) $override
                            : (float) ($selectedProduct?->base_price ?? 0);
                        $rowTotal = $quantity > 0 ? $quantity * $unitPrice : 0;
                    @endphp

                    <tr wire:key="plan-item-row-{{ $index }}" class="align-top">
                        <td class="min-w-72 px-3 py-3">
                            <flux:field>
                                <flux:label class="sr-only">Product</flux:label>
                                <flux:select wire:model.live="items.{{ $index }}.catalog_product_id" name="items.{{ $index }}.catalog_product_id" required>
                                    <option value="">Select a product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->currency }} {{ number_format((float) $product->base_price, 2) }})</option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="items.{{ $index }}.catalog_product_id" />
                            </flux:field>
                        </td>
                        <td class="min-w-32 px-3 py-3">
                            <flux:field>
                                <flux:label class="sr-only">Qty</flux:label>
                                <flux:input wire:model.live.debounce.150ms="items.{{ $index }}.quantity" name="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" required />
                                <flux:error name="items.{{ $index }}.quantity" />
                            </flux:field>
                        </td>
                        <td class="min-w-40 px-3 py-3">
                            <flux:field>
                                <flux:label class="sr-only">Unit Override</flux:label>
                                <flux:input wire:model.live.debounce.150ms="items.{{ $index }}.unit_price_override" name="items.{{ $index }}.unit_price_override" type="number" step="0.01" min="0" placeholder="Optional" />
                                <flux:error name="items.{{ $index }}.unit_price_override" />
                            </flux:field>
                        </td>
                        <td class="px-3 py-3">
                            <p class="mt-2 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ strtoupper($currency) }} {{ number_format($rowTotal, 2) }}
                            </p>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end">
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    wire:click="removeItemRow({{ $index }})"
                                    wire:confirm="Remove this plan item?"
                                    title="Remove Row"
                                    aria-label="Remove Row"
                                >
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
