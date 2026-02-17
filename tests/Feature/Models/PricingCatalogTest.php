<?php

use App\Enums\BillingUnitType;
use App\Enums\CatalogSourceType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use App\Models\Proposal;
use App\Models\ProposalLineItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('creates catalog product with enum casts and user relationship', function (): void {
    $product = CatalogProduct::factory()->create([
        'platform' => PlatformType::Instagram,
        'media_type' => MediaType::Post,
        'billing_unit' => BillingUnitType::Deliverable,
    ]);

    expect($product->user)->toBeInstanceOf(User::class)
        ->and($product->platform)->toBe(PlatformType::Instagram)
        ->and($product->media_type)->toBe(MediaType::Post)
        ->and($product->billing_unit)->toBe(BillingUnitType::Deliverable)
        ->and($product->is_active)->toBeTrue();
});

it('defines user catalog product and plan relationships', function (): void {
    $user = User::factory()->create();

    CatalogProduct::factory()->for($user)->create();
    CatalogPlan::factory()->for($user)->create();

    $catalogProductsReturnType = (new ReflectionMethod(User::class, 'catalogProducts'))
        ->getReturnType()?->getName();
    $catalogPlansReturnType = (new ReflectionMethod(User::class, 'catalogPlans'))
        ->getReturnType()?->getName();

    expect($user->catalogProducts())->toBeInstanceOf(HasMany::class)
        ->and($user->catalogProducts)->toHaveCount(1)
        ->and($catalogProductsReturnType)->toBe(HasMany::class)
        ->and($user->catalogPlans())->toBeInstanceOf(HasMany::class)
        ->and($user->catalogPlans)->toHaveCount(1)
        ->and($catalogPlansReturnType)->toBe(HasMany::class);
});

it('defines catalog plan and plan item relationships', function (): void {
    $plan = CatalogPlan::factory()->create();
    $product = CatalogProduct::factory()->for($plan->user)->create();
    $planItem = CatalogPlanItem::factory()->for($plan)->for($product)->create();

    expect($plan->items())->toBeInstanceOf(HasMany::class)
        ->and($plan->items)->toHaveCount(1)
        ->and($planItem->catalogPlan())->toBeInstanceOf(BelongsTo::class)
        ->and($planItem->catalogProduct())->toBeInstanceOf(BelongsTo::class)
        ->and($planItem->catalogPlan->id)->toBe($plan->id)
        ->and($planItem->catalogProduct->id)->toBe($product->id);
});

it('creates proposal line items with enum casts and proposal relationship', function (): void {
    $proposal = Proposal::factory()->create();

    $lineItem = ProposalLineItem::factory()->for($proposal)->create([
        'source_type' => CatalogSourceType::Product,
        'platform_snapshot' => PlatformType::TikTok,
        'media_type_snapshot' => MediaType::Reel,
        'sort_order' => 2,
    ]);

    expect($lineItem->proposal)->toBeInstanceOf(Proposal::class)
        ->and($lineItem->source_type)->toBe(CatalogSourceType::Product)
        ->and($lineItem->platform_snapshot)->toBe(PlatformType::TikTok)
        ->and($lineItem->media_type_snapshot)->toBe(MediaType::Reel)
        ->and($lineItem->sort_order)->toBe(2);
});

it('defines proposal line items relationship', function (): void {
    $proposal = Proposal::factory()->create();

    ProposalLineItem::factory()->for($proposal)->count(2)->create();

    $lineItemsReturnType = (new ReflectionMethod(Proposal::class, 'lineItems'))
        ->getReturnType()?->getName();

    expect($proposal->lineItems())->toBeInstanceOf(HasMany::class)
        ->and($lineItemsReturnType)->toBe(HasMany::class)
        ->and($proposal->lineItems)->toHaveCount(2);
});
