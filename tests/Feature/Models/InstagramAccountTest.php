<?php

use App\Enums\AccountType;
use App\Enums\SyncStatus;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('creates valid instagram account records with factory defaults', function (): void {
    $account = InstagramAccount::factory()->create();

    expect($account->user)->toBeInstanceOf(User::class)
        ->and($account->instagram_user_id)->not->toBeEmpty()
        ->and($account->username)->not->toBeEmpty()
        ->and($account->account_type)->toBeInstanceOf(AccountType::class)
        ->and($account->sync_status)->toBeInstanceOf(SyncStatus::class)
        ->and($account->token_expires_at)->not->toBeNull();
});

it('supports primary business creator and token expired factory states', function (): void {
    $primaryBusiness = InstagramAccount::factory()->primary()->business()->create();
    $creatorExpired = InstagramAccount::factory()->creator()->tokenExpired()->create();

    expect($primaryBusiness->is_primary)->toBeTrue()
        ->and($primaryBusiness->account_type)->toBe(AccountType::Business)
        ->and($creatorExpired->account_type)->toBe(AccountType::Creator)
        ->and($creatorExpired->token_expires_at->isPast())->toBeTrue();
});

it('stores access tokens encrypted and decrypts them on retrieval', function (): void {
    $plaintextToken = 'igac.test-token-1234567890';

    $account = InstagramAccount::factory()->create([
        'access_token' => $plaintextToken,
    ]);

    $rawToken = DB::table('instagram_accounts')
        ->where('id', $account->id)
        ->value('access_token');

    expect($rawToken)->not->toBe($plaintextToken)
        ->and($account->fresh()->access_token)->toBe($plaintextToken);
});

it('defines user instagram accounts and primary instagram account relationships', function (): void {
    $user = User::factory()->create();

    $secondary = InstagramAccount::factory()->for($user)->create(['is_primary' => false]);
    $primary = InstagramAccount::factory()->for($user)->primary()->create();

    expect($secondary->user->is($user))->toBeTrue()
        ->and($user->instagramAccounts)->toHaveCount(2)
        ->and($user->primaryInstagramAccount->is($primary))->toBeTrue();
});

it('stores long profile picture urls from meta graph responses', function (): void {
    $longUrl = 'https://scontent-mrs2-1.xx.fbcdn.net/v/t51.2885-15/381676632_857008545780239_3038572693511449324_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=7d201b&_nc_ohc=K6_hCaL6Uj4Q7kNvwEMPlgx&_nc_oc=Adm7GqkPFgckFv7KqnHgbo844una9DvKdY327M32AJ8KVyVK4th3QVQjRSaPf2Kjn_s&_nc_zt=23&_nc_ht=scontent-mrs2-1.xx&edm=AGaHXAAEAAAA&oh=00_AfsLbqRakrYIiQG6EtZ5rGNtmYAPOB9AxxUV_ZLNOXsolg&oe=69911255';

    $account = InstagramAccount::factory()->create([
        'profile_picture_url' => $longUrl,
    ]);

    expect($account->fresh()->profile_picture_url)->toBe($longUrl);
});
