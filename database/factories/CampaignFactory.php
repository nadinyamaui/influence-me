<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'proposal_id' => null,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(12),
        ];
    }
}
