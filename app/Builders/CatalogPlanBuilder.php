<?php

namespace App\Builders;

use App\Models\CatalogPlan;
use Illuminate\Database\Eloquent\Builder;

class CatalogPlanBuilder extends Builder
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

        return $this->where(function (self $query) use ($search): void {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%');
        });
    }

    public function filterByActive(?bool $active): self
    {
        if ($active === null) {
            return $this;
        }

        return $this->where('is_active', $active);
    }

    public function withItemsCount(): self
    {
        return $this->withCount('items');
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
            'bundle_price_asc' => $this->orderBy('bundle_price')->orderByDesc('id'),
            'bundle_price_desc' => $this->orderByDesc('bundle_price')->orderByDesc('id'),
            'items_desc' => $this->orderByDesc('items_count')->orderByDesc('id'),
            'items_asc' => $this->orderBy('items_count')->orderByDesc('id'),
            'oldest' => $this->orderBy('created_at')->orderBy('id'),
            default => $this->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    public function createForUser(array $attributes, ?int $userId = null): CatalogPlan
    {
        $userId ??= auth()->id();

        return $this->create(array_merge($attributes, [
            'user_id' => $userId,
        ]));
    }
}
