<?php

use App\Livewire\Pricing\Plans\Form as PlansForm;
use App\Livewire\Pricing\Plans\Index as PlansIndex;
use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use App\Models\User;
use App\Services\Catalog\CatalogPlanService;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

test('guests are redirected from pricing plan pages', function (): void {
    $plan = CatalogPlan::factory()->create();

    $this->get(route('pricing.plans.index'))->assertRedirect(route('login'));
    $this->get(route('pricing.plans.create'))->assertRedirect(route('login'));
    $this->get(route('pricing.plans.edit', $plan))->assertRedirect(route('login'));
});

test('authenticated influencers can view their pricing plans list', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();

    CatalogPlan::factory()->for($user)->create(['name' => 'Owner Plan']);
    CatalogPlan::factory()->for($outsider)->create(['name' => 'Hidden Plan']);

    $this->actingAs($user)
        ->get(route('pricing.plans.index'))
        ->assertSuccessful()
        ->assertSee('Pricing Plans')
        ->assertSee('Owner Plan')
        ->assertDontSee('Hidden Plan')
        ->assertSee('href="'.route('pricing.plans.index').'"', false);
});

test('pricing plans index supports search status and sort filters through builder methods', function (): void {
    $user = User::factory()->create();

    $product = CatalogProduct::factory()->for($user)->create();

    $planOne = CatalogPlan::factory()->for($user)->create([
        'name' => 'Alpha Plan',
        'description' => 'Alpha launch package',
        'is_active' => true,
    ]);

    $planTwo = CatalogPlan::factory()->for($user)->create([
        'name' => 'Zeta Plan',
        'description' => 'Zeta always-on package',
        'is_active' => true,
    ]);

    $archivedPlan = CatalogPlan::factory()->for($user)->create([
        'name' => 'Archived Plan',
        'description' => 'Archived package',
        'is_active' => false,
    ]);

    CatalogPlanItem::factory()->for($planOne)->for($product)->count(1)->create();
    CatalogPlanItem::factory()->for($planTwo)->for($product)->count(2)->create();
    CatalogPlanItem::factory()->for($archivedPlan)->for($product)->count(3)->create();

    Livewire::actingAs($user)
        ->test(PlansIndex::class)
        ->set('search', 'plan')
        ->set('status', 'active')
        ->set('sort', 'name_asc')
        ->assertSeeInOrder(['Alpha Plan', 'Zeta Plan'])
        ->assertDontSee('Archived Plan')
        ->set('sort', 'items_desc')
        ->assertSeeInOrder(['Zeta Plan', 'Alpha Plan'])
        ->set('status', 'archived')
        ->assertSee('Archived Plan')
        ->assertDontSee('Alpha Plan');
});

test('pricing plans list shows empty state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pricing.plans.index'))
        ->assertSuccessful()
        ->assertSee('No pricing plans found for the selected filters.');
});

test('influencer can create pricing plans with nested items', function (): void {
    $user = User::factory()->create();
    $productOne = CatalogProduct::factory()->for($user)->create(['base_price' => 450]);
    $productTwo = CatalogProduct::factory()->for($user)->create(['base_price' => 700]);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', 'Launch Bundle')
        ->set('description', 'Two-deliverable bundle')
        ->set('currency', 'usd')
        ->set('bundle_price', '1000')
        ->set('is_active', true)
        ->set('items', [
            [
                'catalog_product_id' => (string) $productOne->id,
                'quantity' => '2',
                'unit_price_override' => '',
            ],
            [
                'catalog_product_id' => (string) $productTwo->id,
                'quantity' => '1',
                'unit_price_override' => '550',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('pricing.plans.index'));

    $plan = CatalogPlan::query()->where('user_id', $user->id)->where('name', 'Launch Bundle')->first();

    expect($plan)->not->toBeNull();

    $this->assertDatabaseHas('catalog_plans', [
        'id' => $plan->id,
        'user_id' => $user->id,
        'currency' => 'USD',
        'bundle_price' => '1000.00',
        'is_active' => 1,
    ]);

    $this->assertDatabaseHas('catalog_plan_items', [
        'catalog_plan_id' => $plan->id,
        'catalog_product_id' => $productOne->id,
        'quantity' => '2.00',
        'unit_price_override' => null,
    ]);

    $this->assertDatabaseHas('catalog_plan_items', [
        'catalog_plan_id' => $plan->id,
        'catalog_product_id' => $productTwo->id,
        'quantity' => '1.00',
        'unit_price_override' => '550.00',
    ]);
});

test('plan row total preview updates when quantity or unit override changes', function (): void {
    $user = User::factory()->create();
    $product = CatalogProduct::factory()->for($user)->create([
        'base_price' => 100,
        'currency' => 'USD',
    ]);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('items.0.catalog_product_id', (string) $product->id)
        ->assertSee('USD 100.00')
        ->set('items.0.quantity', '2')
        ->assertSee('USD 200.00')
        ->set('items.0.unit_price_override', '75')
        ->assertSee('USD 150.00');
});

test('create form validates plan composition and ownership rules', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();
    $outsiderProduct = CatalogProduct::factory()->for($outsider)->create();

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', '')
        ->set('currency', 'US')
        ->set('bundle_price', '-1')
        ->set('items', [
            [
                'catalog_product_id' => (string) $outsiderProduct->id,
                'quantity' => '0',
                'unit_price_override' => '-1',
            ],
        ])
        ->call('save')
        ->assertHasErrors([
            'name',
            'currency',
            'bundle_price',
            'items.0.catalog_product_id',
            'items.0.quantity',
            'items.0.unit_price_override',
        ]);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', 'No Items Plan')
        ->set('items', [])
        ->call('save')
        ->assertHasErrors(['items']);
});

test('influencer can edit pricing plans and replace nested items', function (): void {
    $user = User::factory()->create();

    $productOne = CatalogProduct::factory()->for($user)->create();
    $productTwo = CatalogProduct::factory()->for($user)->create();

    $plan = CatalogPlan::factory()->for($user)->create([
        'name' => 'Old Plan Name',
        'bundle_price' => 1200,
        'currency' => 'USD',
    ]);

    CatalogPlanItem::factory()->for($plan)->for($productOne)->create([
        'quantity' => 1,
        'unit_price_override' => null,
    ]);

    Livewire::actingAs($user)
        ->test(PlansForm::class, ['plan' => $plan])
        ->set('name', 'Updated Plan Name')
        ->set('description', 'Updated description')
        ->set('bundle_price', '1800.00')
        ->set('currency', 'eur')
        ->set('is_active', false)
        ->set('items', [
            [
                'catalog_product_id' => (string) $productTwo->id,
                'quantity' => '3',
                'unit_price_override' => '600',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('pricing.plans.index'));

    $this->assertDatabaseHas('catalog_plans', [
        'id' => $plan->id,
        'name' => 'Updated Plan Name',
        'description' => 'Updated description',
        'bundle_price' => '1800.00',
        'currency' => 'EUR',
        'is_active' => 0,
    ]);

    $this->assertDatabaseHas('catalog_plan_items', [
        'catalog_plan_id' => $plan->id,
        'catalog_product_id' => $productTwo->id,
        'quantity' => '3.00',
        'unit_price_override' => '600.00',
    ]);

    $this->assertDatabaseMissing('catalog_plan_items', [
        'catalog_plan_id' => $plan->id,
        'catalog_product_id' => $productOne->id,
    ]);
});

test('users cannot access or mutate other user pricing plans', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $plan = CatalogPlan::factory()->for($owner)->create();
    $ownerProduct = CatalogProduct::factory()->for($owner)->create();

    $this->actingAs($outsider)
        ->get(route('pricing.plans.edit', $plan))
        ->assertForbidden();

    expect(function () use ($outsider, $plan, $ownerProduct): void {
        app(CatalogPlanService::class)->update($outsider, $plan, [
            'name' => 'Unauthorized Update',
            'description' => null,
            'currency' => 'USD',
            'bundle_price' => null,
            'is_active' => true,
            'items' => [
                [
                    'catalog_product_id' => (string) $ownerProduct->id,
                    'quantity' => '1',
                    'unit_price_override' => null,
                ],
            ],
        ]);
    })->toThrow(AuthorizationException::class);
});
