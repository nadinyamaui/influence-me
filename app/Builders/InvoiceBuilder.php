<?php

namespace App\Builders;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;

class InvoiceBuilder extends Builder
{
    public function pending(): self
    {
        return $this->whereIn('status', InvoiceStatus::pendingValues());
    }
}
