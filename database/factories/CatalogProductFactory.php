<?php

namespace Database\Factories;

use App\Enums\BillingUnitType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogProductFactory extends Factory
{
    protected $model = CatalogProduct::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'platform' => fake()->randomElement(PlatformType::cases()),
            'media_type' => fake()->randomElement(MediaType::cases()),
            'billing_unit' => fake()->randomElement(BillingUnitType::cases()),
            'base_price' => fake()->randomFloat(2, 50, 5000),
            'currency' => 'USD',
            'is_active' => true,
        ];
    }
}
