<?php

namespace App\Models;

use App\Builders\UserBuilder;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

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

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function primarySocialAccount(): HasOne
    {
        return $this->hasOne(SocialAccount::class)->where('is_primary', true);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function catalogProducts(): HasMany
    {
        return $this->hasMany(CatalogProduct::class);
    }

    public function catalogPlans(): HasMany
    {
        return $this->hasMany(CatalogPlan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPost::class);
    }

    public static function resolveSocialAccount(int $accountId): SocialAccount
    {
        $account = Auth::user()?->socialAccounts()
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

    public static function resolveInvoice(int $invoiceId): Invoice
    {
        $invoice = Auth::user()?->invoices()
            ->whereKey($invoiceId)
            ->first();

        if ($invoice === null) {
            abort(404);
        }

        return $invoice;
    }

    public static function accounts(): Collection
    {
        return Auth::user()?->socialAccounts()
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
