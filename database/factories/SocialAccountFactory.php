<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Enums\SocialNetwork;
use App\Enums\SyncStatus;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'user_id' => User::factory(),
            'social_network' => SocialNetwork::Instagram,
            'social_network_user_id' => (string) fake()->unique()->numberBetween(1000000000, 9999999999),
            'username' => $username,
            'name' => fake()->name(),
            'biography' => fake()->sentence(12),
            'profile_picture_url' => fake()->imageUrl(320, 320, 'people'),
            'account_type' => fake()->randomElement([AccountType::Business, AccountType::Creator]),
            'followers_count' => fake()->numberBetween(500, 500000),
            'following_count' => fake()->numberBetween(100, 5000),
            'media_count' => fake()->numberBetween(10, 3000),
            'access_token' => 'igac.'.fake()->regexify('[A-Za-z0-9]{32}'),
            'token_expires_at' => now()->addDays(55),
            'is_primary' => false,
            'last_synced_at' => now()->subMinutes(fake()->numberBetween(5, 180)),
            'sync_status' => SyncStatus::Idle,
            'last_sync_error' => null,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_primary' => true,
        ]);
    }

    public function business(): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_type' => AccountType::Business,
        ]);
    }

    public function creator(): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_type' => AccountType::Creator,
        ]);
    }

    public function tokenExpired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'token_expires_at' => now()->subDay(),
        ]);
    }
}
