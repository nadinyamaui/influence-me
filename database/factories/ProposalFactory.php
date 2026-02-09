<?php

namespace Database\Factories;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proposal>
 */
class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'client_id' => Client::factory()->for($user),
            'title' => fake()->sentence(6),
            'content' => sprintf(
                "# %s\n\n%s\n\n## Deliverables\n\n- %s\n- %s",
                fake()->sentence(3),
                fake()->paragraph(3),
                fake()->sentence(4),
                fake()->sentence(4),
            ),
            'status' => ProposalStatus::Draft,
            'revision_notes' => null,
            'sent_at' => null,
            'responded_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProposalStatus::Draft,
            'sent_at' => null,
            'responded_at' => null,
            'revision_notes' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProposalStatus::Sent,
            'sent_at' => now()->subHour(),
            'responded_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProposalStatus::Approved,
            'sent_at' => now()->subDays(2),
            'responded_at' => now()->subDay(),
            'revision_notes' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProposalStatus::Rejected,
            'sent_at' => now()->subDays(2),
            'responded_at' => now()->subDay(),
            'revision_notes' => null,
        ]);
    }

    public function revised(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProposalStatus::Revised,
            'sent_at' => now()->subDays(2),
            'responded_at' => now()->subDay(),
            'revision_notes' => fake()->sentence(10),
        ]);
    }
}
