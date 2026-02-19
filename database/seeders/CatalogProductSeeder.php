<?php

namespace Database\Seeders;

use App\Models\CatalogProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create();

        CatalogProduct::factory()
            ->count(500)
            ->create([
                'user_id' => $user->id,
            ]);
    }
}
