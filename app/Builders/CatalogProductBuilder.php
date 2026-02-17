<?php

namespace App\Builders;

use App\Enums\BillingUnitType;
use App\Enums\PlatformType;
use App\Models\CatalogProduct;
use Illuminate\Database\Eloquent\Builder;

class CatalogProductBuilder extends Builder
{
    public function forUser(?int $userId = null): self
    {
        $userId ??= auth()->id();

        return $this->where('user_id', $userId);
    }

    public function search(string $term): self
    {
        $search = trim($term);
        if ($search === '') {
            return $this;
        }

        return $this->where('name', 'like', '%'.$search.'%');
    }

    public function filterByPlatform(string $platform): self
    {
        if (! in_array($platform, PlatformType::values(), true)) {
            return $this;
        }

        return $this->where('platform', $platform);
    }

    public function filterByBillingUnit(string $billingUnit): self
    {
        if (! in_array($billingUnit, BillingUnitType::values(), true)) {
            return $this;
        }

        return $this->where('billing_unit', $billingUnit);
    }

    public function activeOnly(): self
    {
        return $this->where('is_active', true);
    }

    public function latestFirst(): self
    {
        return $this->orderByDesc('created_at');
    }

    public function createForUser(array $attributes, ?int $userId = null): CatalogProduct
    {
        $userId ??= auth()->id();

        return $this->create(array_merge($attributes, [
            'user_id' => $userId,
        ]));
    }
}
