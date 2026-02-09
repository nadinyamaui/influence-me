<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstagramMedia extends Model
{
    /** @use HasFactory<\Database\Factories\InstagramMediaFactory> */
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
            'media_type' => MediaType::class,
            'published_at' => 'datetime',
            'engagement_rate' => 'decimal:2',
        ];
    }

    /**
     * Get the Instagram account this media belongs to.
     */
    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    /**
     * Get the clients linked to this media for campaigns.
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'campaign_media')
            ->withPivot('campaign_name', 'notes')
            ->withTimestamps();
    }
}
