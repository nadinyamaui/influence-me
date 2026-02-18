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

    public function search(?string $term): self
    {
        $search = trim((string) $term);
        if ($search === '') {
            return $this;
        }

        return $this->where('name', 'like', '%'.$search.'%');
    }

    public function filterByPlatform(?string $platform): self
    {
        if (! in_array($platform, PlatformType::values(), true)) {
            return $this;
        }

        return $this->where('platform', $platform);
    }

    public function filterByActive(?bool $active): self
    {
        if ($active === null) {
            return $this;
        }

        return $this->where('is_active', $active);
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

    public function applySort(string $sort): self
    {
        return match ($sort) {
            'name_asc' => $this->orderBy('name')->orderByDesc('id'),
            'name_desc' => $this->orderByDesc('name')->orderByDesc('id'),
            'price_asc' => $this->orderBy('base_price')->orderByDesc('id'),
            'price_desc' => $this->orderByDesc('base_price')->orderByDesc('id'),
            'oldest' => $this->orderBy('created_at')->orderBy('id'),
            default => $this->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    public function createForUser(array $attributes, ?int $userId = null): CatalogProduct
    {
        $userId ??= auth()->id();

        return $this->create(array_merge($attributes, [
            'user_id' => $userId,
        ]));
    }
}
