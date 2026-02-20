<?php

namespace Database\Seeders;

use App\Enums\SocialNetwork;
use App\Models\CatalogProduct;
use App\Models\SocialAccount;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User']
        );

        $user->socialAccounts()->firstOrCreate(
            [
                'social_network' => SocialNetwork::Instagram->value,
                'social_network_user_id' => '17841400000000000',
            ],
            SocialAccount::factory()
                ->primary()
                ->business()
                ->state(['username' => 'testinfluencer'])
                ->make([
                    'user_id' => $user->id,
                    'social_network' => SocialNetwork::Instagram->value,
                    'social_network_user_id' => '17841400000000000',
                ])
                ->toArray()
        );

        if (CatalogProduct::query()->doesntExist()) {
            $this->call(CatalogProductSeeder::class);
        }
    }
}
