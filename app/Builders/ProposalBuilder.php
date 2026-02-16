<?php

namespace App\Builders;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Builder;

class ProposalBuilder extends Builder
{
    public function forUser(?int $userId = null): self
    {
        $userId ??= auth()->id();

        return $this->where('user_id', $userId);
    }

    public function forClient(int $clientId): self
    {
        return $this->where('client_id', $clientId);
    }

    public function filterByStatus(string $status): self
    {
        if (! in_array($status, ProposalStatus::values(), true)) {
            return $this;
        }

        return $this->where('status', $status);
    }

    public function latestFirst(): self
    {
        return $this->orderByDesc('created_at');
    }
}
