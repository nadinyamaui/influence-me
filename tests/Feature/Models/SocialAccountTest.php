<?php

use App\Enums\AccountType;
use App\Enums\SyncStatus;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('creates valid instagram account records with factory defaults', function (): void {
    $account = SocialAccount::factory()->create();

    expect($account->user)->toBeInstanceOf(User::class)
        ->and($account->social_network_user_id)->not->toBeEmpty()
        ->and($account->username)->not->toBeEmpty()
        ->and($account->account_type)->toBeInstanceOf(AccountType::class)
        ->and($account->sync_status)->toBeInstanceOf(SyncStatus::class)
        ->and($account->token_expires_at)->not->toBeNull();
});

it('supports primary business creator and token expired factory states', function (): void {
    $primaryBusiness = SocialAccount::factory()->primary()->business()->create();
    $creatorExpired = SocialAccount::factory()->creator()->tokenExpired()->create();

    expect($primaryBusiness->is_primary)->toBeTrue()
        ->and($primaryBusiness->account_type)->toBe(AccountType::Business)
        ->and($creatorExpired->account_type)->toBe(AccountType::Creator)
        ->and($creatorExpired->token_expires_at->isPast())->toBeTrue();
});

it('stores access tokens encrypted and decrypts them on retrieval', function (): void {
    $plaintextToken = 'igac.test-token-1234567890';

    $account = SocialAccount::factory()->create([
        'access_token' => $plaintextToken,
    ]);

    $rawToken = DB::table('social_accounts')
        ->where('id', $account->id)
        ->value('access_token');

    expect($rawToken)->not->toBe($plaintextToken)
        ->and($account->fresh()->access_token)->toBe($plaintextToken);
});

it('defines user instagram accounts and primary instagram account relationships', function (): void {
    $user = User::factory()->create();

    $secondary = SocialAccount::factory()->for($user)->create(['is_primary' => false]);
    $primary = SocialAccount::factory()->for($user)->primary()->create();

    expect($secondary->user->is($user))->toBeTrue()
        ->and($user->socialAccounts)->toHaveCount(2)
        ->and($user->primarySocialAccount->is($primary))->toBeTrue();
});
