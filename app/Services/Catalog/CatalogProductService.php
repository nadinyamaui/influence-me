<?php

namespace App\Services\Catalog;

use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CatalogProductService
{
    public function create(User $user, array $payload): CatalogProduct
    {
        return CatalogProduct::query()->createForUser($this->normalizePayload($payload), $user->id);
    }

    public function update(User $user, CatalogProduct $catalogProduct, array $payload): CatalogProduct
    {
        $this->ensureOwner($user, $catalogProduct);

        $catalogProduct->update($this->normalizePayload($payload));

        return $catalogProduct->refresh();
    }

    public function archive(User $user, CatalogProduct $catalogProduct): CatalogProduct
    {
        return $this->setArchivedState($user, $catalogProduct, true);
    }

    public function unarchive(User $user, CatalogProduct $catalogProduct): CatalogProduct
    {
        return $this->setArchivedState($user, $catalogProduct, false);
    }

    private function setArchivedState(User $user, CatalogProduct $catalogProduct, bool $archived): CatalogProduct
    {
        $this->ensureOwner($user, $catalogProduct);

        $catalogProduct->update([
            'is_active' => ! $archived,
        ]);

        return $catalogProduct->refresh();
    }

    private function ensureOwner(User $user, CatalogProduct $catalogProduct): void
    {
        if ($catalogProduct->user_id !== $user->id) {
            throw new AuthorizationException('You are not authorized to manage this product.');
        }
    }

    private function normalizePayload(array $payload): array
    {
        return [
            'name' => $payload['name'],
            'platform' => $payload['platform'],
            'media_type' => $payload['media_type'] ?: null,
            'billing_unit' => $payload['billing_unit'],
            'base_price' => $payload['base_price'],
            'currency' => strtoupper($payload['currency']),
            'is_active' => (bool) $payload['is_active'],
        ];
    }
}
