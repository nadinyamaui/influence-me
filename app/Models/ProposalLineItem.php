<?php

namespace App\Models;

use App\Builders\ProposalLineItemBuilder;
use App\Enums\CatalogSourceType;
use App\Enums\MediaType;
use App\Enums\PlatformType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalLineItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): ProposalLineItemBuilder
    {
        return new ProposalLineItemBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'source_type' => CatalogSourceType::class,
            'platform_snapshot' => PlatformType::class,
            'media_type_snapshot' => MediaType::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
