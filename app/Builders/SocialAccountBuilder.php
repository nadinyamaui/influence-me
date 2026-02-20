<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class SocialAccountBuilder extends Builder
{
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function filterByAccount(string $accountId): self
    {
        if ($accountId === 'all') {
            return $this;
        }

        return $this->whereKey((int) $accountId);
    }
}
