<?php

namespace App\Livewire\Athenas;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Storefront extends Component
{
    #[Url(as: 'buscar')]
    public string $search = '';

    #[Url(as: 'categoria')]
    public string $categoryFilter = '';

    #[Url(as: 'acabado')]
    public string $finishFilter = '';

    public array $cart = [];

    public bool $showCart = false;

    public function mount(): void
    {
        $this->cart = session('athenas_cart', []);
    }

    #[Computed]
    public function products(): array
    {
        $products = collect(config('athenas.products'))
            ->when($this->search !== '', fn ($c) => $c->filter(
                fn ($p) => str_contains(mb_strtolower($p['name']), mb_strtolower($this->search))
                    || str_contains(mb_strtolower($p['code']), mb_strtolower($this->search))
            ))
            ->when($this->categoryFilter !== '', fn ($c) => $c->where('category', $this->categoryFilter))
            ->when($this->finishFilter !== '', fn ($c) => $c->where('finish', $this->finishFilter))
            ->values()
            ->all();

        return $products;
    }

    #[Computed]
    public function categories(): array
    {
        return collect(config('athenas.products'))
            ->pluck('category')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function finishes(): array
    {
        return collect(config('athenas.products'))
            ->pluck('finish')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function cartItems(): array
    {
        $products = collect(config('athenas.products'))->keyBy('code');

        return collect($this->cart)
            ->map(function ($qty, $code) use ($products) {
                $product = $products->get($code);
                if (! $product) {
                    return null;
                }

                return [
                    ...$product,
                    'quantity' => $qty,
                    'subtotal' => $product['price'] * $qty,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    #[Computed]
    public function cartTotal(): float
    {
        return collect($this->cartItems)->sum('subtotal');
    }

    #[Computed]
    public function cartCount(): int
    {
        return array_sum($this->cart);
    }

    public function addToCart(string $code): void
    {
        $this->cart[$code] = ($this->cart[$code] ?? 0) + 1;
        $this->persistCart();
    }

    public function removeFromCart(string $code): void
    {
        unset($this->cart[$code]);
        $this->persistCart();
    }

    public function incrementItem(string $code): void
    {
        if (isset($this->cart[$code])) {
            $this->cart[$code]++;
            $this->persistCart();
        }
    }

    public function decrementItem(string $code): void
    {
        if (isset($this->cart[$code])) {
            $this->cart[$code]--;
            if ($this->cart[$code] <= 0) {
                unset($this->cart[$code]);
            }
            $this->persistCart();
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->persistCart();
    }

    public function toggleCart(): void
    {
        $this->showCart = ! $this->showCart;
    }

    #[Computed]
    public function whatsappUrl(): string
    {
        $phone = config('athenas.whatsapp');
        $lines = ["*Pedido Athenas Boutique*\n"];

        foreach ($this->cartItems as $item) {
            $lines[] = "- {$item['name']} (Ref: {$item['code']}) x{$item['quantity']} = \u{20AC}" . number_format($item['subtotal'], 2);
        }

        $lines[] = "\n*Total: \u{20AC}" . number_format($this->cartTotal, 2) . '*';

        $message = implode("\n", $lines);

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->finishFilter = '';
    }

    private function persistCart(): void
    {
        session(['athenas_cart' => $this->cart]);
    }

    public function render()
    {
        return view('pages.athenas.storefront')
            ->layout('pages.athenas.layout', [
                'title' => 'Athenas Boutique - Accesorios',
            ]);
    }
}
