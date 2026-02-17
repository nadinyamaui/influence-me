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

    public function search(string $term): self
    {
        $search = trim($term);
        if ($search === '') {
            return $this;
        }

        return $this->where('name', 'like', '%'.$search.'%');
    }

    public function activeOnly(): self
    {
        return $this->where('is_active', true);
    }

    public function latestFirst(): self
    {
        return $this->orderByDesc('created_at');
    }

    public function createForUser(array $attributes, ?int $userId = null): CatalogPlan
    {
        $userId ??= auth()->id();

        return $this->create(array_merge($attributes, [
            'user_id' => $userId,
        ]));
    }
}
