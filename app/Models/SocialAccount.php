<?php

namespace App\Models;

use App\Builders\SocialAccountBuilder;
use App\Enums\AccountType;
use App\Enums\SocialNetwork;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function newEloquentBuilder($query): SocialAccountBuilder
    {
        return new SocialAccountBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'social_network' => SocialNetwork::class,
            'account_type' => AccountType::class,
            'sync_status' => SyncStatus::class,
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_primary' => 'boolean',
            'access_token' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instagramMedia(): HasMany
    {
        return $this->hasMany(InstagramMedia::class);
    }

    public function audienceDemographics(): HasMany
    {
        return $this->hasMany(AudienceDemographic::class);
    }

    public function followerSnapshots(): HasMany
    {
        return $this->hasMany(FollowerSnapshot::class);
    }
}
