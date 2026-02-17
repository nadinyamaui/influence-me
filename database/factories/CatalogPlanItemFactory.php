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
            'catalog_product_id' => CatalogProduct::factory(),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit_price_override' => fake()->optional()->randomFloat(2, 10, 5000),
        ];
    }
}
