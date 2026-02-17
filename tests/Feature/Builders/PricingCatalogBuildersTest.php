<?php

use App\Enums\BillingUnitType;
use App\Enums\CatalogSourceType;
use App\Enums\PlatformType;
use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\ProposalLineItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use function Pest\Laravel\actingAs;

it('scopes catalog products to owner and applies filters', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    CatalogProduct::factory()->for($owner)->create([
        'name' => 'Instagram Reel',
        'platform' => PlatformType::Instagram,
        'billing_unit' => BillingUnitType::Deliverable,
        'is_active' => true,
    ]);
    CatalogProduct::factory()->for($owner)->create([
        'name' => 'TikTok Package',
        'platform' => PlatformType::TikTok,
        'billing_unit' => BillingUnitType::Package,
        'is_active' => false,
    ]);
    CatalogProduct::factory()->for($outsider)->create([
        'name' => 'Instagram Post',
        'platform' => PlatformType::Instagram,
        'billing_unit' => BillingUnitType::Deliverable,
        'is_active' => true,
    ]);

    $results = CatalogProduct::query()
        ->forUser($owner->id)
        ->search('instagram')
        ->filterByPlatform(PlatformType::Instagram->value)
        ->filterByBillingUnit(BillingUnitType::Deliverable->value)
        ->activeOnly()
        ->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->user_id)->toBe($owner->id)
        ->and($results->first()->name)->toBe('Instagram Reel');
});

it('creates catalog product and plan records with scoped builder helpers', function (): void {
    $user = User::factory()->create();

    $product = CatalogProduct::query()->createForUser([
        'name' => 'Launch Post',
        'platform' => PlatformType::Instagram,
        'media_type' => null,
        'billing_unit' => BillingUnitType::Deliverable,
        'base_price' => 500,
        'currency' => 'USD',
        'is_active' => true,
    ], $user->id);

    $plan = CatalogPlan::query()->createForUser([
        'name' => 'Starter Plan',
        'description' => null,
        'bundle_price' => 1200,
        'currency' => 'USD',
        'is_active' => true,
    ], $user->id);

    expect($product->user_id)->toBe($user->id)
        ->and($plan->user_id)->toBe($user->id);
});

it('scopes and creates catalog plan items', function (): void {
    $plan = CatalogPlan::factory()->create();
    $product = CatalogProduct::factory()->for($plan->user)->create();
    actingAs($plan->user);

    CatalogPlanItem::query()->createForPlan([
        'catalog_product_id' => $product->id,
        'quantity' => 2,
        'unit_price_override' => 450,
    ], $plan->id);

    $items = CatalogPlanItem::query()->forPlan($plan->id)->get();

    expect($items)->toHaveCount(1)
        ->and($items->first()->catalog_plan_id)->toBe($plan->id)
        ->and($items->first()->catalog_product_id)->toBe($product->id);
});

it('blocks creating plan items with products from another influencer', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $plan = CatalogPlan::factory()->for($owner)->create();
    $outsiderProduct = CatalogProduct::factory()->for($outsider)->create();
    actingAs($owner);

    expect(fn (): CatalogPlanItem => CatalogPlanItem::query()->createForPlan([
        'catalog_product_id' => $outsiderProduct->id,
        'quantity' => 1,
        'unit_price_override' => null,
    ], $plan->id))->toThrow(ModelNotFoundException::class);
});

it('blocks creating plan items on another influencers plan', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $ownerPlan = CatalogPlan::factory()->for($owner)->create();
    $ownerProduct = CatalogProduct::factory()->for($owner)->create();

    actingAs($outsider);

    expect(fn (): CatalogPlanItem => CatalogPlanItem::query()->createForPlan([
        'catalog_product_id' => $ownerProduct->id,
        'quantity' => 1,
        'unit_price_override' => null,
    ], $ownerPlan->id))->toThrow(ModelNotFoundException::class);
});

it('scopes and orders proposal line items with builder helpers', function (): void {
    $proposal = Proposal::factory()->create();
    actingAs($proposal->user);

    ProposalLineItem::query()->createForProposal([
        'source_type' => CatalogSourceType::Custom,
        'source_id' => null,
        'name_snapshot' => 'A',
        'description_snapshot' => null,
        'platform_snapshot' => null,
        'media_type_snapshot' => null,
        'quantity' => 1,
        'unit_price' => 100,
        'line_total' => 100,
        'sort_order' => 2,
    ], $proposal->id);

    ProposalLineItem::query()->createForProposal([
        'source_type' => CatalogSourceType::Custom,
        'source_id' => null,
        'name_snapshot' => 'B',
        'description_snapshot' => null,
        'platform_snapshot' => null,
        'media_type_snapshot' => null,
        'quantity' => 1,
        'unit_price' => 200,
        'line_total' => 200,
        'sort_order' => 1,
    ], $proposal->id);

    $ordered = ProposalLineItem::query()
        ->forProposal($proposal->id)
        ->ordered()
        ->pluck('name_snapshot')
        ->all();

    expect($ordered)->toBe(['B', 'A']);
});

it('blocks creating line items on another influencers proposal', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $client = Client::factory()->for($owner)->create();
    $proposal = Proposal::factory()->for($owner)->for($client)->create();

    actingAs($outsider);

    expect(fn (): ProposalLineItem => ProposalLineItem::query()->createForProposal([
        'source_type' => CatalogSourceType::Custom,
        'source_id' => null,
        'name_snapshot' => 'Unauthorized',
        'description_snapshot' => null,
        'platform_snapshot' => null,
        'media_type_snapshot' => null,
        'quantity' => 1,
        'unit_price' => 10,
        'line_total' => 10,
        'sort_order' => 1,
    ], $proposal->id))->toThrow(ModelNotFoundException::class);
});

it('blocks creating line items with cross-tenant catalog sources', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $client = Client::factory()->for($owner)->create();
    $proposal = Proposal::factory()->for($owner)->for($client)->create();
    $outsiderProduct = CatalogProduct::factory()->for($outsider)->create();
    $outsiderPlan = CatalogPlan::factory()->for($outsider)->create();

    actingAs($owner);

    expect(fn (): ProposalLineItem => ProposalLineItem::query()->createForProposal([
        'source_type' => CatalogSourceType::Product,
        'source_id' => $outsiderProduct->id,
        'name_snapshot' => 'Unauthorized Product',
        'description_snapshot' => null,
        'platform_snapshot' => null,
        'media_type_snapshot' => null,
        'quantity' => 1,
        'unit_price' => 10,
        'line_total' => 10,
        'sort_order' => 1,
    ], $proposal->id))->toThrow(ModelNotFoundException::class);

    expect(fn (): ProposalLineItem => ProposalLineItem::query()->createForProposal([
        'source_type' => CatalogSourceType::Plan,
        'source_id' => $outsiderPlan->id,
        'name_snapshot' => 'Unauthorized Plan',
        'description_snapshot' => null,
        'platform_snapshot' => null,
        'media_type_snapshot' => null,
        'quantity' => 1,
        'unit_price' => 10,
        'line_total' => 10,
        'sort_order' => 1,
    ], $proposal->id))->toThrow(ModelNotFoundException::class);
});
