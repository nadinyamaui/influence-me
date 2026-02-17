<?php

namespace App\Models;

use App\Builders\CatalogProductBuilder;
use App\Enums\BillingUnitType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): CatalogProductBuilder
    {
        return new CatalogProductBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'platform' => PlatformType::class,
            'media_type' => MediaType::class,
            'billing_unit' => BillingUnitType::class,
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function planItems(): HasMany
    {
        return $this->hasMany(CatalogPlanItem::class);
    }
}
