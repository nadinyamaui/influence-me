<?php

test('athenas codex catalog page renders and stores the catalog snapshot in session', function (): void {
    $response = $this->get(route('athenas-codex.index'));

    $response->assertSuccessful();
    $response->assertSee('Athenas Boutique');
    $response->assertSee('Dalia');
    $response->assertSee('Compra directa por WhatsApp');

    expect(session('athenas_codex.catalog.products'))
        ->toBeArray()
        ->not->toBeEmpty();
});

test('athenas codex catalog filters products by category', function (): void {
    $response = $this->get(route('athenas-codex.index', [
        'category' => 'necklaces',
    ]));

    $response->assertSuccessful();
    $response->assertSee('Mystic');
    $response->assertSee('Spyros');
    $response->assertSee('Alexander');
});

test('customers can add products to the athenas codex cart from the public page', function (): void {
    $response = $this->from(route('athenas-codex.index'))
        ->post(route('athenas-codex.cart.items.store'), [
            'product' => 'dalia-wave',
            'quantity' => 2,
            'search' => '',
            'category' => 'all',
            'availability' => 'all',
        ]);

    $response->assertRedirect(route('athenas-codex.index').'#catalogo');

    expect(session('athenas_codex.cart.items'))
        ->toMatchArray([
            'dalia-wave' => 2,
        ]);

    $this->get(route('athenas-codex.index'))
        ->assertSuccessful()
        ->assertSee('EUR 3,00')
        ->assertSee('Pedido actual');
});

test('sold out products cannot be added to the athenas codex cart', function (): void {
    $response = $this->from(route('athenas-codex.index'))
        ->post(route('athenas-codex.cart.items.store'), [
            'product' => 'ava-ring',
            'quantity' => 1,
            'search' => '',
            'category' => 'all',
            'availability' => 'all',
        ]);

    $response->assertRedirect(route('athenas-codex.index'));
    $response->assertSessionHasErrors('product');

    expect(session('athenas_codex.cart.items', []))->toBe([]);
});

test('customers can update and remove cart items from the athenas codex page', function (): void {
    session()->put('athenas_codex.cart.items', [
        'dalia-wave' => 1,
    ]);

    $this->from(route('athenas-codex.index'))
        ->patch(route('athenas-codex.cart.items.update', 'dalia-wave'), [
            'quantity' => 3,
            'search' => '',
            'category' => 'all',
            'availability' => 'all',
        ])
        ->assertRedirect(route('athenas-codex.index').'#carrito');

    expect(session('athenas_codex.cart.items'))
        ->toMatchArray([
            'dalia-wave' => 3,
        ]);

    $this->from(route('athenas-codex.index'))
        ->delete(route('athenas-codex.cart.items.destroy', 'dalia-wave'), [
            'search' => '',
            'category' => 'all',
            'availability' => 'all',
        ])
        ->assertRedirect(route('athenas-codex.index').'#carrito');

    expect(session('athenas_codex.cart.items', []))->toBe([]);
});
