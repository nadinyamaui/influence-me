<?php

namespace Database\Factories;

use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogPlanItemFactory extends Factory
{
    protected $model = CatalogPlanItem::class;

    public function definition(): array
    {
        return [
            'catalog_plan_id' => CatalogPlan::factory(),
            'catalog_product_id' => function (array $attributes): int {
                $catalogPlan = CatalogPlan::query()->findOrFail($attributes['catalog_plan_id']);

                return CatalogProduct::factory()
                    ->for($catalogPlan->user)
                    ->create()
                    ->id;
            },
            'quantity' => fake()->randomFloat(2, 1, 10),
        ];
    }
}
