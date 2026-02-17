<?php

namespace Database\Factories;

use App\Models\FollowerSnapshot;
use App\Models\InstagramAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowerSnapshotFactory extends Factory
{
    protected $model = FollowerSnapshot::class;

    public function definition(): array
    {
        return [
            'instagram_account_id' => InstagramAccount::factory(),
            'followers_count' => fake()->numberBetween(100, 500000),
            'recorded_at' => now()->subDays(fake()->numberBetween(0, 120))->toDateString(),
        ];
    }
}
