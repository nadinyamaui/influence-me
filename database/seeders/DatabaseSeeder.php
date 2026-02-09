<?php

namespace Database\Seeders;

use App\Models\InstagramAccount;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()
            ->has(
                InstagramAccount::factory()
                    ->primary()
                    ->business()
                    ->state([
                        'instagram_user_id' => '17841400000000000',
                        'username' => 'testinfluencer',
                    ]),
                'instagramAccounts'
            )
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
    }
}
