<?php

namespace Database\Factories;

use App\Enums\CatalogSourceType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use App\Models\Proposal;
use App\Models\ProposalLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProposalLineItemFactory extends Factory
{
    protected $model = ProposalLineItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $unitPrice = fake()->randomFloat(2, 50, 5000);

        return [
            'proposal_id' => Proposal::factory(),
            'source_type' => fake()->randomElement(CatalogSourceType::cases()),
            'source_id' => null,
            'name_snapshot' => fake()->words(3, true),
            'description_snapshot' => fake()->sentence(12),
            'platform_snapshot' => fake()->randomElement(PlatformType::cases()),
            'media_type_snapshot' => fake()->randomElement(MediaType::cases()),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($quantity * $unitPrice, 2),
            'sort_order' => 0,
        ];
    }
}
