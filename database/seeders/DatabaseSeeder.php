<?php

namespace Database\Seeders;

use App\Models\CatalogProduct;
use App\Models\InstagramAccount;
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

        $user->instagramAccounts()->firstOrCreate(
            ['instagram_user_id' => '17841400000000000'],
            InstagramAccount::factory()
                ->primary()
                ->business()
                ->state(['username' => 'testinfluencer'])
                ->make(['user_id' => $user->id, 'instagram_user_id' => '17841400000000000'])
                ->toArray()
        );

        $this->call(CatalogProductSeeder::class);
    }
}
