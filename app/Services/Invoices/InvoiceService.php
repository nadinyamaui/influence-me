<?php

namespace App\Services\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\CatalogPlan;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    public function create(User $user, array $payload): Invoice
    {
        return DB::transaction(function () use ($user, $payload): Invoice {
            $this->ensureClientOwned($user, (int) $payload['client_id']);

            $invoice = Invoice::query()->create([
                'user_id' => $user->id,
                'client_id' => (int) $payload['client_id'],
                'status' => InvoiceStatus::Draft,
                'due_date' => $payload['due_date'],
                'tax_rate' => $payload['tax_rate'] ?? 0,
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->replaceItems($invoice, $payload['items'] ?? []);
            $invoice->calculateTotals();

            return $invoice->refresh()->load(['client', 'items']);
        });
    }

    public function update(User $user, Invoice $invoice, array $payload): Invoice
    {
        $this->ensureOwner($user, $invoice);
        $this->ensureClientOwned($user, (int) $payload['client_id']);

        return DB::transaction(function () use ($invoice, $payload): Invoice {
            $invoice->update([
                'client_id' => (int) $payload['client_id'],
                'due_date' => $payload['due_date'],
                'tax_rate' => $payload['tax_rate'] ?? 0,
                'notes' => $payload['notes'] ?? null,
            ]);

            $this->replaceItems($invoice, $payload['items'] ?? []);
            $invoice->calculateTotals();

            return $invoice->refresh()->load(['client', 'items']);
        });
    }

    public function planPrice(int $planId, int $userId): float
    {
        $plan = CatalogPlan::query()
            ->forUser($userId)
            ->with(['items.catalogProduct:id,base_price'])
            ->findOrFail($planId);

        if ($plan->bundle_price !== null) {
            return round((float) $plan->bundle_price, 2);
        }

        return round((float) $plan->items->sum(
            fn ($item): float => (float) $item->quantity * (float) ($item->catalogProduct?->base_price ?? 0)
        ), 2);
    }

    private function replaceItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            if (($item['catalog_product_id'] ?? null) !== null && ($item['catalog_plan_id'] ?? null) !== null) {
                throw new InvalidArgumentException('Invoice items cannot reference both a product and a plan.');
            }

            InvoiceItem::query()->createForInvoice([
                'catalog_product_id' => $item['catalog_product_id'] ?? null,
                'catalog_plan_id' => $item['catalog_plan_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
            ], $invoice->id);
        }
    }

    private function ensureClientOwned(User $user, int $clientId): void
    {
        $exists = Client::query()
            ->whereKey($clientId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $exists) {
            throw new AuthorizationException('You are not authorized to use this client.');
        }
    }

    private function ensureOwner(User $user, Invoice $invoice): void
    {
        if ($invoice->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to manage this invoice.');
        }
    }
}
