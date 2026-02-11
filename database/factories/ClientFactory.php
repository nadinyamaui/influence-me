<?php

namespace Database\Factories;

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

    public function definition(): array
    {
        $type = fake()->randomElement([ClientType::Brand, ClientType::Individual]);

        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'company_name' => $type === ClientType::Brand ? fake()->company() : null,
            'type' => $type,
            'phone' => fake()->phoneNumber(),
            'notes' => fake()->sentence(10),
        ];
    }

    public function brand(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ClientType::Brand,
            'company_name' => fake()->company(),
        ]);
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ClientType::Individual,
            'company_name' => null,
        ]);
    }
}
