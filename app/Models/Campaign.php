<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public function instagramMedia(): BelongsToMany
    {
        return $this->belongsToMany(InstagramMedia::class, 'campaign_media')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public static function resolveForUser(int $campaignId): self
    {
        $userId = auth()->id();

        if ($userId === null) {
            abort(404);
        }

        $campaign = self::query()
            ->whereKey($campaignId)
            ->whereHas('client', fn (Builder $builder): Builder => $builder->where('user_id', $userId))
            ->first();

        if ($campaign === null) {
            abort(404);
        }

        return $campaign;
    }
}
