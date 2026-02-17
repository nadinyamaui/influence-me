<?php

namespace App\Models;

use App\Builders\CatalogPlanBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): CatalogPlanBuilder
    {
        return new CatalogPlanBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'bundle_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CatalogPlanItem::class);
    }
}
