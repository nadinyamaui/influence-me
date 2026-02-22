<?php

namespace Database\Factories;

use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['VAT', 'Sales Tax', 'GST']),
            'rate' => fake()->randomFloat(2, 0, 25),
            'is_active' => true,
        ];
    }
}
