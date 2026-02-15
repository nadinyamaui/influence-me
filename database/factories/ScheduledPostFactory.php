<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Enums\ScheduledPostStatus;
use App\Models\Client;
use App\Models\InstagramAccount;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledPostFactory extends Factory
{
    protected $model = ScheduledPost::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => fn (array $attributes): int => Client::factory()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'campaign_id' => null,
            'instagram_account_id' => fn (array $attributes): int => InstagramAccount::factory()->create([
                'user_id' => $attributes['user_id'],
            ])->id,
            'title' => fake()->sentence(6),
            'description' => fake()->optional()->paragraph(),
            'media_type' => fake()->randomElement(MediaType::cases()),
            'scheduled_at' => now()->addHours(fake()->numberBetween(1, 168)),
            'status' => ScheduledPostStatus::Planned,
        ];
    }

    public function planned(): static
    {
        return $this->state(fn (): array => [
            'status' => ScheduledPostStatus::Planned,
            'scheduled_at' => now()->addHours(fake()->numberBetween(1, 168)),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ScheduledPostStatus::Published,
            'scheduled_at' => now()->subHours(fake()->numberBetween(1, 168)),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ScheduledPostStatus::Cancelled,
            'scheduled_at' => now()->addHours(fake()->numberBetween(1, 168)),
        ]);
    }
}
