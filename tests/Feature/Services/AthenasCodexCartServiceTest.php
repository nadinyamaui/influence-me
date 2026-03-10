<?php

use App\Services\AthenasCodex\CartService;
use Illuminate\Validation\ValidationException;

test('athenas codex cart service builds a whatsapp checkout url with line items and total', function (): void {
    session()->put('athenas_codex.cart.items', [
        'dalia-wave' => 2,
        'mystic-layered' => 1,
    ]);

    $summary = app(CartService::class)->summary();

    expect($summary['item_count'])->toBe(3)
        ->and($summary['product_count'])->toBe(2)
        ->and($summary['formatted_total'])->toBe('EUR 8,00')
        ->and($summary['checkout_url'])->toContain('https://wa.me/584124652070')
        ->and($summary['checkout_url'])->toContain(rawurlencode('Dalia (DALIA) x2'))
        ->and($summary['checkout_url'])->toContain(rawurlencode('Mystic (MYSTIC) x1'))
        ->and($summary['checkout_url'])->toContain(rawurlencode('Total: EUR 8,00'));
});

test('athenas codex cart service rejects sold out products', function (): void {
    expect(fn () => app(CartService::class)->add('ava-ring', 1))
        ->toThrow(ValidationException::class);
});
