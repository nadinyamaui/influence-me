<?php

namespace App\Builders;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Builder;

class InvoiceItemBuilder extends Builder
{
    public function forInvoice(int $invoiceId): self
    {
        return $this->where('invoice_id', $invoiceId);
    }

    public function createForInvoice(array $attributes, int $invoiceId): InvoiceItem
    {
        return $this->create(array_merge($attributes, [
            'invoice_id' => $invoiceId,
        ]));
    }
}
