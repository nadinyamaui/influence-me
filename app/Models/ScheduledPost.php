<?php

namespace App\Models;

use App\Builders\ScheduledPostBuilder;
use App\Enums\MediaType;
use App\Enums\ScheduledPostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPost extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): ScheduledPostBuilder
    {
        return new ScheduledPostBuilder($query);
    }

    public static function resolveOwnedPost(int $scheduledPostId, int $userId): self
    {
        return self::query()
            ->where('user_id', $userId)
            ->whereKey($scheduledPostId)
            ->firstOrFail();
    }

    protected function casts(): array
    {
        return [
            'status' => ScheduledPostStatus::class,
            'media_type' => MediaType::class,
            'scheduled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }
}
