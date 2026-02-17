<?php

namespace App\Builders;

use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use Illuminate\Database\Eloquent\Builder;

class CatalogPlanItemBuilder extends Builder
{
    public function forPlan(int $catalogPlanId): self
    {
        return $this->where('catalog_plan_id', $catalogPlanId);
    }

    public function createForPlan(array $attributes, int $catalogPlanId, ?int $userId = null): CatalogPlanItem
    {
        $userId ??= auth()->id();

        $catalogPlan = CatalogPlan::query()
            ->select(['id', 'user_id'])
            ->where('user_id', $userId)
            ->findOrFail($catalogPlanId);

        $catalogProductId = $attributes['catalog_product_id'] ?? null;

        CatalogProduct::query()
            ->whereKey($catalogProductId)
            ->where('user_id', $catalogPlan->user_id)
            ->firstOrFail();

        return $this->create(array_merge($attributes, [
            'catalog_plan_id' => $catalogPlanId,
        ]));
    }
}
