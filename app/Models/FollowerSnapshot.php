<?php

namespace App\Models;

use App\Builders\FollowerSnapshotBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowerSnapshot extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): FollowerSnapshotBuilder
    {
        return new FollowerSnapshotBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'recorded_at' => 'date',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }
}
