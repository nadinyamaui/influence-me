<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'socialite_user_type',
        'socialite_user_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function instagramAccounts(): HasMany
    {
        return $this->hasMany(InstagramAccount::class);
    }

    public function primaryInstagramAccount(): HasOne
    {
        return $this->hasOne(InstagramAccount::class)->where('is_primary', true);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public static function resolveInstagramAccount(int $accountId): InstagramAccount
    {
        $account = Auth::user()?->instagramAccounts()
            ->whereKey($accountId)
            ->first();

        if ($account === null) {
            abort(404);
        }

        return $account;
    }

    public static function resolveClient(int $clientId): Client
    {
        $client = Auth::user()?->clients()
            ->whereKey($clientId)
            ->first();

        if ($client === null) {
            abort(404);
        }

        return $client;
    }

    public static function resolveProposal(int $proposalId): Proposal
    {
        $proposal = Auth::user()?->proposals()
            ->whereKey($proposalId)
            ->first();

        if ($proposal === null) {
            abort(404);
        }

        return $proposal;
    }

    public static function accounts(): Collection
    {
        return Auth::user()?->instagramAccounts()
            ->orderBy('username')
            ->get(['id', 'username']) ?? collect();
    }

    public static function availableClients(): Collection
    {
        return Auth::user()?->clients()
            ->orderBy('name')
            ->get(['id', 'name']) ?? collect();
    }
}
