<?php

use App\Enums\BillingUnitType;
use App\Enums\PlatformType;
use App\Livewire\Pricing\Products\Form as ProductsForm;
use App\Livewire\Pricing\Products\Index as ProductsIndex;
use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('guests are redirected from pricing product pages', function (): void {
    $product = CatalogProduct::factory()->create();

    $this->get(route('pricing.products.index'))->assertRedirect(route('login'));
    $this->get(route('pricing.products.create'))->assertRedirect(route('login'));
    $this->get(route('pricing.products.edit', $product))->assertRedirect(route('login'));
});

test('authenticated influencers can view their pricing products list', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();

    CatalogProduct::factory()->for($user)->create(['name' => 'Owner Product']);
    CatalogProduct::factory()->for($outsider)->create(['name' => 'Hidden Product']);

    $this->actingAs($user)
        ->get(route('pricing.products.index'))
        ->assertSuccessful()
        ->assertSee('Pricing Products')
        ->assertSee('Owner Product')
        ->assertDontSee('Hidden Product')
        ->assertSee('href="'.route('pricing.products.index').'"', false);
});

test('pricing products index supports search platform status and sort filters through builder methods', function (): void {
    $user = User::factory()->create();

    CatalogProduct::factory()->for($user)->create([
        'name' => 'Z Product',
        'platform' => PlatformType::Instagram,
        'is_active' => true,
        'base_price' => 900,
    ]);

    CatalogProduct::factory()->for($user)->create([
        'name' => 'A Product',
        'platform' => PlatformType::Instagram,
        'is_active' => true,
        'base_price' => 100,
    ]);

    CatalogProduct::factory()->for($user)->create([
        'name' => 'Archived Product',
        'platform' => PlatformType::TikTok,
        'is_active' => false,
        'base_price' => 500,
    ]);

    Livewire::actingAs($user)
        ->test(ProductsIndex::class)
        ->set('search', 'product')
        ->set('platform', PlatformType::Instagram->value)
        ->set('status', 'active')
        ->set('sort', 'name_asc')
        ->assertSeeInOrder(['A Product', 'Z Product'])
        ->assertDontSee('Archived Product')
        ->set('status', 'archived')
        ->set('platform', 'all')
        ->assertSee('Archived Product')
        ->assertDontSee('A Product')
        ->set('sort', 'price_desc')
        ->assertSee('Archived Product');
});

test('pricing products list shows empty state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pricing.products.index'))
        ->assertSuccessful()
        ->assertSee('No pricing products found for the selected filters.');
});

test('influencer can create pricing products', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductsForm::class)
        ->set('name', 'Instagram Reel Deliverable')
        ->set('platform', PlatformType::Instagram->value)
        ->set('media_type', 'reel')
        ->set('billing_unit', BillingUnitType::Deliverable->value)
        ->set('base_price', '450.00')
        ->set('currency', 'usd')
        ->set('is_active', true)
        ->call('save')
        ->assertRedirect(route('pricing.products.index'));

    $this->assertDatabaseHas('catalog_products', [
        'user_id' => $user->id,
        'name' => 'Instagram Reel Deliverable',
        'platform' => PlatformType::Instagram->value,
        'media_type' => 'reel',
        'billing_unit' => BillingUnitType::Deliverable->value,
        'base_price' => '450.00',
        'currency' => 'USD',
        'is_active' => 1,
    ]);
});

test('create form validates pricing product fields', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ProductsForm::class)
        ->set('name', '')
        ->set('platform', 'unknown')
        ->set('media_type', 'unknown')
        ->set('billing_unit', 'unknown')
        ->set('base_price', '-1')
        ->set('currency', 'US')
        ->call('save')
        ->assertHasErrors([
            'name',
            'platform',
            'media_type',
            'billing_unit',
            'base_price',
            'currency',
        ]);
});

test('influencer can edit pricing products', function (): void {
    $user = User::factory()->create();
    $product = CatalogProduct::factory()->for($user)->create([
        'name' => 'Old Name',
        'currency' => 'USD',
    ]);

    Livewire::actingAs($user)
        ->test(ProductsForm::class, ['product' => $product])
        ->set('name', 'Updated Product')
        ->set('platform', PlatformType::TikTok->value)
        ->set('media_type', '')
        ->set('billing_unit', BillingUnitType::Package->value)
        ->set('base_price', '1300.00')
        ->set('currency', 'eur')
        ->set('is_active', false)
        ->call('save')
        ->assertRedirect(route('pricing.products.index'));

    $this->assertDatabaseHas('catalog_products', [
        'id' => $product->id,
        'name' => 'Updated Product',
        'platform' => PlatformType::TikTok->value,
        'media_type' => null,
        'billing_unit' => BillingUnitType::Package->value,
        'base_price' => '1300.00',
        'currency' => 'EUR',
        'is_active' => 0,
    ]);
});

test('archiving and unarchiving products works from list page', function (): void {
    $user = User::factory()->create();
    $product = CatalogProduct::factory()->for($user)->create(['is_active' => true]);

    Livewire::actingAs($user)
        ->test(ProductsIndex::class)
        ->call('archive', $product->id);

    $this->assertDatabaseHas('catalog_products', [
        'id' => $product->id,
        'is_active' => 0,
    ]);

    Livewire::actingAs($user)
        ->test(ProductsIndex::class)
        ->call('unarchive', $product->id);

    $this->assertDatabaseHas('catalog_products', [
        'id' => $product->id,
        'is_active' => 1,
    ]);
});

test('users cannot access or mutate other user pricing products', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $product = CatalogProduct::factory()->for($owner)->create();

    $this->actingAs($outsider)
        ->get(route('pricing.products.edit', $product))
        ->assertForbidden();

    expect(function () use ($outsider, $product): void {
        Livewire::actingAs($outsider)
            ->test(ProductsIndex::class)
            ->call('archive', $product->id);
    })->toThrow(ModelNotFoundException::class);
});
