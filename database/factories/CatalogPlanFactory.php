<?php

namespace Database\Factories;

use App\Models\CatalogPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogPlanFactory extends Factory
{
    protected $model = CatalogPlan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(12),
            'bundle_price' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'USD',
            'is_active' => true,
        ];
    }
}
