<?php

namespace App\Models;

use App\Builders\AudienceDemographicBuilder;
use App\Enums\DemographicType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudienceDemographic extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): AudienceDemographicBuilder
    {
        return new AudienceDemographicBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'type' => DemographicType::class,
            'value' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }
}
