<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\SocialAccountMedia;
use App\Models\SocialAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialAccountMediaFactory extends Factory
{
    protected $model = SocialAccountMedia::class;

    public function definition(): array
    {
        return [
            'social_account_id' => SocialAccount::factory(),
            'social_account_media_id' => (string) fake()->unique()->numberBetween(100000000000000000, 999999999999999999),
            'media_type' => fake()->randomElement([MediaType::Post, MediaType::Reel, MediaType::Story]),
            'caption' => fake()->sentence(12),
            'permalink' => fake()->url(),
            'media_url' => fake()->imageUrl(1080, 1080),
            'thumbnail_url' => fake()->imageUrl(1080, 1080),
            'published_at' => now()->subHours(fake()->numberBetween(1, 240)),
            'like_count' => fake()->numberBetween(20, 1500),
            'comments_count' => fake()->numberBetween(2, 250),
            'saved_count' => fake()->numberBetween(0, 120),
            'shares_count' => fake()->numberBetween(0, 80),
            'reach' => fake()->numberBetween(500, 50000),
            'impressions' => fake()->numberBetween(600, 70000),
            'engagement_rate' => fake()->randomFloat(2, 1, 12),
        ];
    }

    public function post(): static
    {
        return $this->state(fn (array $attributes): array => [
            'media_type' => MediaType::Post,
        ]);
    }

    public function reel(): static
    {
        return $this->state(fn (array $attributes): array => [
            'media_type' => MediaType::Reel,
        ]);
    }

    public function story(): static
    {
        return $this->state(fn (array $attributes): array => [
            'media_type' => MediaType::Story,
        ]);
    }

    public function highEngagement(): static
    {
        return $this->state(fn (array $attributes): array => [
            'like_count' => fake()->numberBetween(5000, 50000),
            'comments_count' => fake()->numberBetween(500, 5000),
            'saved_count' => fake()->numberBetween(300, 3500),
            'shares_count' => fake()->numberBetween(250, 2500),
            'reach' => fake()->numberBetween(20000, 250000),
            'impressions' => fake()->numberBetween(30000, 400000),
            'engagement_rate' => fake()->randomFloat(2, 18, 40),
        ]);
    }
}
