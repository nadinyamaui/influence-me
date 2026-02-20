<?php

namespace Database\Factories;

use App\Enums\DemographicType;
use App\Models\AudienceDemographic;
use App\Models\SocialAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class AudienceDemographicFactory extends Factory
{
    protected $model = AudienceDemographic::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            DemographicType::Age,
            DemographicType::Gender,
            DemographicType::City,
            DemographicType::Country,
        ]);

        return [
            'social_account_id' => SocialAccount::factory(),
            'type' => $type,
            'dimension' => match ($type) {
                DemographicType::Age => fake()->randomElement(['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+']),
                DemographicType::Gender => fake()->randomElement(['Male', 'Female']),
                DemographicType::City => fake()->city(),
                DemographicType::Country => fake()->country(),
            },
            'value' => fake()->randomFloat(2, 0.01, 99.99),
            'recorded_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    public function age(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DemographicType::Age,
            'dimension' => fake()->randomElement(['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+']),
        ]);
    }

    public function gender(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DemographicType::Gender,
            'dimension' => fake()->randomElement(['Male', 'Female']),
        ]);
    }

    public function city(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DemographicType::City,
            'dimension' => fake()->city(),
        ]);
    }

    public function country(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => DemographicType::Country,
            'dimension' => fake()->country(),
        ]);
    }
}
