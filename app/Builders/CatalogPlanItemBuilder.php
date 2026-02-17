<?php

namespace App\Builders;

use App\Models\CatalogPlanItem;
use Illuminate\Database\Eloquent\Builder;

class CatalogPlanItemBuilder extends Builder
{
    public function forPlan(int $catalogPlanId): self
    {
        return $this->where('catalog_plan_id', $catalogPlanId);
    }

    public function createForPlan(array $attributes, int $catalogPlanId): CatalogPlanItem
    {
        return $this->create(array_merge($attributes, [
            'catalog_plan_id' => $catalogPlanId,
        ]));
    }
}
