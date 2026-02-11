<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $unitPrice = fake()->randomFloat(2, 100, 5000);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->randomElement([
                'Instagram Post - Product Review',
                'Reel - Campaign Launch',
                'Story Set - 3 Frames',
                'UGC Content Package',
            ]),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($quantity * $unitPrice, 2),
        ];
    }
}
