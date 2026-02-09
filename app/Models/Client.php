<?php

namespace App\Models;

use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
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
            'type' => ClientType::class,
        ];
    }

    /**
     * Get the influencer that owns this client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the portal account for this client.
     */
    public function clientUser(): HasOne
    {
        return $this->hasOne(ClientUser::class);
    }

    /**
     * Get proposals associated with this client.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * Get invoices associated with this client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get campaign-linked Instagram media for this client.
     */
    public function instagramMedia(): BelongsToMany
    {
        return $this->belongsToMany(InstagramMedia::class, 'campaign_media')
            ->withPivot('campaign_name', 'notes')
            ->withTimestamps();
    }
}
