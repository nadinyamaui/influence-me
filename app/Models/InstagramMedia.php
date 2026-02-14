<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstagramMedia extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'media_type' => MediaType::class,
            'published_at' => 'datetime',
            'engagement_rate' => 'decimal:2',
        ];
    }

    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_media')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public static function resolveForUser(int $mediaId): self
    {
        $userId = auth()->id();

        if ($userId === null) {
            abort(404);
        }

        $media = self::query()
            ->whereKey($mediaId)
            ->whereHas('instagramAccount', fn (Builder $builder): Builder => $builder->where('user_id', $userId))
            ->first();

        if ($media === null) {
            abort(404);
        }

        return $media;
    }
}
