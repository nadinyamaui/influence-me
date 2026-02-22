<?php

namespace App\Builders;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Builder;

class TaxRateBuilder extends Builder
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

        return $this->where('label', 'like', '%'.$search.'%');
    }

    public function filterByActive(?bool $active): self
    {
        if ($active === null) {
            return $this;
        }

        return $this->where('is_active', $active);
    }

    public function applySort(string $sort): self
    {
        return match ($sort) {
            'label_asc' => $this->orderBy('label')->orderByDesc('id'),
            'label_desc' => $this->orderByDesc('label')->orderByDesc('id'),
            'rate_asc' => $this->orderBy('rate')->orderByDesc('id'),
            'rate_desc' => $this->orderByDesc('rate')->orderByDesc('id'),
            'oldest' => $this->orderBy('created_at')->orderBy('id'),
            default => $this->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    public function createForUser(array $attributes, ?int $userId = null): TaxRate
    {
        $userId ??= auth()->id();

        return $this->create(array_merge($attributes, [
            'user_id' => $userId,
        ]));
    }
}
