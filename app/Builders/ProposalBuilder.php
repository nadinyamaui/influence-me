<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class ProposalBuilder extends Builder
{
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function forClient(int $clientId): self
    {
        return $this->where('client_id', $clientId);
    }

    public function latestFirst(): self
    {
        return $this->orderByDesc('created_at');
    }
}
