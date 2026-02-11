<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 15000);
        $taxRate = fake()->randomFloat(2, 0, 12);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => 'pending-'.(string) Str::uuid(),
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->addDays(fake()->numberBetween(7, 45))->toDateString(),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => round($subtotal + $taxAmount, 2),
            'stripe_payment_link' => null,
            'stripe_session_id' => null,
            'paid_at' => null,
            'notes' => fake()->optional()->sentence(10),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Draft,
            'paid_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Sent,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Paid,
            'paid_at' => now()->subDay(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => InvoiceStatus::Overdue,
            'due_date' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
            'paid_at' => null,
        ]);
    }
}
