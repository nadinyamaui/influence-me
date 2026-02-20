<?php

namespace App\Services\Catalog;

use App\Models\CatalogPlan;
use App\Models\CatalogPlanItem;
use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CatalogPlanService
{
    public function create(User $user, array $payload): CatalogPlan
    {
        return DB::transaction(function () use ($user, $payload): CatalogPlan {
            $items = $payload['items'] ?? [];

            $this->ensureProductsOwned($user, $items);

            $catalogPlan = CatalogPlan::query()->createForUser(
                $this->normalizePlanPayload($payload),
                $user->id,
            );

            $this->replaceItems($catalogPlan, $items);

            return $catalogPlan->load(['items.catalogProduct']);
        });
    }

    public function update(User $user, CatalogPlan $catalogPlan, array $payload): CatalogPlan
    {
        $this->ensureOwner($user, $catalogPlan);

        return DB::transaction(function () use ($user, $catalogPlan, $payload): CatalogPlan {
            $items = $payload['items'] ?? [];

            $this->ensureProductsOwned($user, $items);

            $catalogPlan->update($this->normalizePlanPayload($payload));
            $this->replaceItems($catalogPlan, $items);

            return $catalogPlan->refresh()->load(['items.catalogProduct']);
        });
    }

    private function replaceItems(CatalogPlan $catalogPlan, array $items): void
    {
        $catalogPlan->items()->delete();

        foreach ($items as $item) {
            CatalogPlanItem::query()->createForPlan(
                $this->normalizeItemPayload($item),
                $catalogPlan->id,
                $catalogPlan->user_id,
            );
        }
    }

    private function ensureOwner(User $user, CatalogPlan $catalogPlan): void
    {
        if ($catalogPlan->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to manage this plan.');
        }
    }

    private function ensureProductsOwned(User $user, array $items): void
    {
        $productIds = collect($items)
            ->pluck('catalog_product_id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return;
        }

        $ownedCount = CatalogProduct::query()
            ->whereIn('id', $productIds->all())
            ->where('user_id', $user->id)
            ->count();

        if ($ownedCount !== $productIds->count()) {
            throw new AuthorizationException('One or more selected products are not available.');
        }
    }

    private function normalizePlanPayload(array $payload): array
    {
        return [
            'name' => $payload['name'],
            'description' => $payload['description'] ?: null,
            'bundle_price' => $payload['bundle_price'],
            'currency' => strtoupper($payload['currency']),
            'is_active' => (bool) $payload['is_active'],
        ];
    }

    private function normalizeItemPayload(array $item): array
    {
        return [
            'catalog_product_id' => (int) $item['catalog_product_id'],
            'quantity' => $item['quantity'],
            'unit_price_override' => $item['unit_price_override'] ?: null,
        ];
    }
}
