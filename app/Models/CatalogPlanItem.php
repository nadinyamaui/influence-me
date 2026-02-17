<?php

namespace App\Models;

use App\Builders\CatalogPlanItemBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogPlanItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): CatalogPlanItemBuilder
    {
        return new CatalogPlanItemBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price_override' => 'decimal:2',
        ];
    }

    public function catalogPlan(): BelongsTo
    {
        return $this->belongsTo(CatalogPlan::class);
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(CatalogProduct::class);
    }
}
