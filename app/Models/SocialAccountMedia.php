<?php

namespace App\Models;

use App\Builders\SocialAccountMediaBuilder;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SocialAccountMedia extends Model
{
    use HasFactory;

    protected $table = 'instagram_media';

    protected $guarded = [];

    public function newEloquentBuilder($query): SocialAccountMediaBuilder
    {
        return new SocialAccountMediaBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'media_type' => MediaType::class,
            'published_at' => 'datetime',
            'engagement_rate' => 'decimal:2',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_media', 'instagram_media_id', 'campaign_id')
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
            ->whereHas('socialAccount', fn (Builder $builder): Builder => $builder->where('user_id', $userId))
            ->first();

        if ($media === null) {
            abort(404);
        }

        return $media;
    }
}
