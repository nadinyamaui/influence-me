<?php

namespace App\Builders;

use App\Enums\AnalyticsPeriod;
use Illuminate\Database\Eloquent\Builder;

class FollowerSnapshotBuilder extends Builder
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

    public function forAnalyticsPeriod(AnalyticsPeriod $period): self
    {
        $periodStart = $period->startsAt();

        if ($periodStart === null) {
            return $this;
        }

        return $this->where('recorded_at', '>=', $periodStart);
    }

    public function orderedByRecordedAt(): self
    {
        return $this->orderBy('recorded_at');
    }
}
