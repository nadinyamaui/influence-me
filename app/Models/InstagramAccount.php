<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstagramAccount extends Model
{
    /** @use HasFactory<\Database\Factories\InstagramAccountFactory> */
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
            'account_type' => AccountType::class,
            'sync_status' => SyncStatus::class,
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_primary' => 'boolean',
            'access_token' => 'encrypted',
        ];
    }

    /**
     * Get the influencer that owns the Instagram account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all media for this Instagram account.
     */
    public function instagramMedia(): HasMany
    {
        return $this->hasMany(InstagramMedia::class);
    }

    /**
     * Get audience demographics for this Instagram account.
     */
    public function audienceDemographics(): HasMany
    {
        return $this->hasMany(AudienceDemographic::class);
    }
}
