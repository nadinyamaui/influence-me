<?php

namespace App\Builders;

use App\Enums\DemographicType;
use Illuminate\Database\Eloquent\Builder;

class AudienceDemographicBuilder extends Builder
{
    public function forUser(int $userId): self
    {
        return $this->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', $userId));
    }

    public function filterByAccount(string $accountId): self
    {
        if ($accountId === 'all') {
            return $this;
        }

        return $this->where('instagram_account_id', (int) $accountId);
    }

    public function ofType(DemographicType $type): self
    {
        return $this->where('type', $type->value);
    }

    public function orderedByValueDesc(): self
    {
        return $this->orderByDesc('value')
            ->orderBy('dimension');
    }
}
