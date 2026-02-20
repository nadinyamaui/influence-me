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

test('pricing plans index normalizes invalid filter values', function (): void {
    $user = User::factory()->create();

    $product = CatalogProduct::factory()->for($user)->create();

    $activePlan = CatalogPlan::factory()->for($user)->create([
        'name' => 'Active Plan',
        'is_active' => true,
    ]);

    $archivedPlan = CatalogPlan::factory()->for($user)->create([
        'name' => 'Archived Plan',
        'is_active' => false,
    ]);

    CatalogPlanItem::factory()->for($activePlan)->for($product)->create();
    CatalogPlanItem::factory()->for($archivedPlan)->for($product)->create();

    Livewire::actingAs($user)
        ->test(PlansIndex::class)
        ->set('status', 'invalid-status')
        ->assertSet('status', 'active')
        ->set('sort', 'invalid-sort')
        ->assertSet('sort', 'newest')
        ->assertSee('Active Plan')
        ->assertDontSee('Archived Plan');
});

test('pricing plans list shows empty state', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pricing.plans.index'))
        ->assertSuccessful()
        ->assertSee('No pricing plans found for the selected filters.');
});

test('plan form hides unit override and row total columns', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->assertDontSee('Unit Override')
        ->assertDontSee('Row Total');
});

test('plan form only lists active products owned by influencer', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();

    $activeOwnedProduct = CatalogProduct::factory()->for($user)->create([
        'name' => 'Owned Active Product',
        'is_active' => true,
    ]);

    $archivedOwnedProduct = CatalogProduct::factory()->for($user)->create([
        'name' => 'Owned Archived Product',
        'is_active' => false,
    ]);

    $outsiderActiveProduct = CatalogProduct::factory()->for($outsider)->create([
        'name' => 'Outsider Active Product',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->assertSee($activeOwnedProduct->name)
        ->assertDontSee($archivedOwnedProduct->name)
        ->assertDontSee($outsiderActiveProduct->name);
});

test('plan form supports adding and removing item rows', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->assertSet('items', [[
            'catalog_product_id' => '',
            'quantity' => '1',
        ]])
        ->call('addItemRow');

    expect($component->get('items'))->toHaveCount(2);

    $component
        ->set('items.0.quantity', '2')
        ->set('items.1.quantity', '3')
        ->call('removeItemRow', 0);

    expect($component->get('items'))->toBe([[
        'catalog_product_id' => '',
        'quantity' => '3',
    ]]);

    $component->call('removeItemRow', 0);

    expect($component->get('items'))->toBe([[
        'catalog_product_id' => '',
        'quantity' => '3',
    ]]);
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
            ],
            [
                'catalog_product_id' => (string) $productTwo->id,
                'quantity' => '1',
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
    ]);

    $this->assertDatabaseHas('catalog_plan_items', [
        'catalog_plan_id' => $plan->id,
        'catalog_product_id' => $productTwo->id,
        'quantity' => '1.00',
    ]);
});

test('create form validates plan composition and ownership rules', function (): void {
    $user = User::factory()->create();
    $outsider = User::factory()->create();
    $ownedProduct = CatalogProduct::factory()->for($user)->create();
    $outsiderProduct = CatalogProduct::factory()->for($outsider)->create();
    $archivedOwnedProduct = CatalogProduct::factory()->for($user)->create(['is_active' => false]);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', '')
        ->set('currency', 'US')
        ->set('bundle_price', '-1')
        ->set('items', [
            [
                'catalog_product_id' => (string) $outsiderProduct->id,
                'quantity' => '0',
            ],
        ])
        ->call('save')
        ->assertHasErrors([
            'name',
            'currency',
            'bundle_price',
            'items.0.catalog_product_id',
            'items.0.quantity',
        ]);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', 'Archived Product Plan')
        ->set('bundle_price', '100')
        ->set('items', [
            [
                'catalog_product_id' => (string) $archivedOwnedProduct->id,
                'quantity' => '1',
            ],
        ])
        ->call('save')
        ->assertHasErrors(['items.0.catalog_product_id']);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', 'Missing Bundle Price')
        ->set('bundle_price', '')
        ->set('items', [
            [
                'catalog_product_id' => (string) $ownedProduct->id,
                'quantity' => '1',
            ],
        ])
        ->call('save')
        ->assertHasErrors(['bundle_price']);

    Livewire::actingAs($user)
        ->test(PlansForm::class)
        ->set('name', 'No Items Plan')
        ->set('bundle_price', '100')
        ->set('items', [])
        ->call('save')
        ->assertHasErrors(['items']);
});

test('plan edit form seeds an empty row when existing plan has no items', function (): void {
    $user = User::factory()->create();
    $plan = CatalogPlan::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(PlansForm::class, ['plan' => $plan])
        ->assertSet('items', [[
            'catalog_product_id' => '',
            'quantity' => '1',
        ]]);
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
                ],
            ],
        ]);
    })->toThrow(AuthorizationException::class);
});

test('service rejects archived products in plan composition payloads', function (): void {
    $user = User::factory()->create();
    $archivedProduct = CatalogProduct::factory()->for($user)->create([
        'is_active' => false,
    ]);

    expect(function () use ($user, $archivedProduct): void {
        app(CatalogPlanService::class)->create($user, [
            'name' => 'Archived Product Attempt',
            'description' => '0',
            'bundle_price' => 100,
            'currency' => 'USD',
            'is_active' => true,
            'items' => [
                [
                    'catalog_product_id' => $archivedProduct->id,
                    'quantity' => 1,
                ],
            ],
        ]);
    })->toThrow(AuthorizationException::class);
});

test('service preserves description value when set to zero-like string', function (): void {
    $user = User::factory()->create();
    $product = CatalogProduct::factory()->for($user)->create();

    app(CatalogPlanService::class)->create($user, [
        'name' => 'Zero Description Plan',
        'description' => '0',
        'bundle_price' => 100,
        'currency' => 'USD',
        'is_active' => true,
        'items' => [
            [
                'catalog_product_id' => $product->id,
                'quantity' => 1,
            ],
        ],
    ]);

    $this->assertDatabaseHas('catalog_plans', [
        'user_id' => $user->id,
        'name' => 'Zero Description Plan',
        'description' => '0',
    ]);
});
