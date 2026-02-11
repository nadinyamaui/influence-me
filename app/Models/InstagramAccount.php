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
    use HasFactory;

    protected $guarded = [];

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
}
