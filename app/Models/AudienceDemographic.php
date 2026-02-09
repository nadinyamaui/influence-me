<?php

namespace App\Models;

use App\Enums\DemographicType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudienceDemographic extends Model
{
    /** @use HasFactory<\Database\Factories\AudienceDemographicFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DemographicType::class,
            'value' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the Instagram account this demographic entry belongs to.
     */
    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }
}
