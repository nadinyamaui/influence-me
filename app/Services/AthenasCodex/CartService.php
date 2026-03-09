<?php

namespace App\Services\AthenasCodex;

use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_KEY = 'athenas_codex.cart.items';

    public function __construct(
        private readonly CatalogService $catalogService,
    ) {}

    public function add(string $sku, int $quantity = 1): array
    {
        $product = $this->catalogService->findOrFail($sku);

        if (! $product['available']) {
            throw ValidationException::withMessages([
                'product' => 'Este producto esta agotado por ahora.',
            ]);
        }

        $cart = $this->cart();
        $cart[$sku] = ($cart[$sku] ?? 0) + $quantity;

        $this->storeCart($cart);

        return $this->summary();
    }

    public function update(string $sku, int $quantity): array
    {
        $this->catalogService->findOrFail($sku);

        $cart = $this->cart();
        $cart[$sku] = $quantity;

        $this->storeCart($cart);

        return $this->summary();
    }

    public function remove(string $sku): array
    {
        $cart = $this->cart();

        unset($cart[$sku]);

        $this->storeCart($cart);

        return $this->summary();
    }

    public function clear(): array
    {
        session()->forget(self::SESSION_KEY);
        session()->forget('athenas_codex.cart.summary');

        return $this->summary();
    }

    public function summary(): array
    {
        $products = collect($this->catalogService->all())->keyBy('sku');
        $items = collect($this->cart())
            ->map(function (int $quantity, string $sku) use ($products): ?array {
                $product = $products->get($sku);

                if ($product === null) {
                    return null;
                }

                $lineTotal = round($quantity * (float) $product['price'], 2);

                return [
                    ...$product,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                    'formatted_line_total' => $this->catalogService->formatMoney($lineTotal),
                ];
            })
            ->filter()
            ->values();

        $total = round((float) $items->sum('line_total'), 2);
        $summary = [
            'items' => $items->all(),
            'item_count' => (int) $items->sum('quantity'),
            'product_count' => $items->count(),
            'total' => $total,
            'formatted_total' => $this->catalogService->formatMoney($total),
            'checkout_url' => $items->isEmpty() ? null : $this->checkoutUrl($items->all(), $total),
            'is_empty' => $items->isEmpty(),
        ];

        session()->put('athenas_codex.cart.summary', $summary);

        return $summary;
    }

    private function cart(): array
    {
        return collect(session(self::SESSION_KEY, []))
            ->map(fn ($quantity): int => max((int) $quantity, 0))
            ->filter(fn (int $quantity): bool => $quantity > 0)
            ->all();
    }

    private function storeCart(array $cart): void
    {
        session()->put(self::SESSION_KEY, $cart);
    }

    private function checkoutUrl(array $items, float $total): string
    {
        $lines = [config('athenas_codex.contact.whatsapp_message_intro'), ''];

        foreach ($items as $item) {
            $lines[] = "- {$item['name']} ({$item['code']}) x{$item['quantity']} - {$item['formatted_line_total']}";
        }

        $lines[] = '';
        $lines[] = 'Total: '.$this->catalogService->formatMoney($total);
        $lines[] = '';
        $lines[] = 'Quedo atento(a) a disponibilidad, pago y entrega.';

        return 'https://wa.me/'.config('athenas_codex.contact.whatsapp_number').'?text='.rawurlencode(implode("\n", $lines));
    }
}
