<?php

namespace App\Models;

use App\Builders\InvoiceItemBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): InvoiceItemBuilder
    {
        return new InvoiceItemBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'catalog_product_id' => 'integer',
            'catalog_plan_id' => 'integer',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class);
    }

    public function catalogPlan(): BelongsTo
    {
        return $this->belongsTo(CatalogPlan::class);
    }
}
