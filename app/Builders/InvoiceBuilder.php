<?php

namespace App\Builders;

use App\Enums\InvoiceStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class InvoiceBuilder extends Builder
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
        if (! in_array($status, InvoiceStatus::values(), true)) {
            return $this;
        }

        return $this->where('status', $status);
    }

    public function pending(): self
    {
        return $this->whereIn('status', InvoiceStatus::pendingValues());
    }

    public function overdue(): self
    {
        return $this->where('status', InvoiceStatus::Overdue);
    }

    public function paidInMonth(CarbonInterface $date): self
    {
        return $this->where('status', InvoiceStatus::Paid)
            ->whereYear('paid_at', $date->year)
            ->whereMonth('paid_at', $date->month);
    }

    public function latestFirst(): self
    {
        return $this->orderByDesc('created_at');
    }
}
