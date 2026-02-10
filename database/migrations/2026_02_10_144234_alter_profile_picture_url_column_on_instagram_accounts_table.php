<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE instagram_accounts MODIFY profile_picture_url TEXT NULL');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE instagram_accounts ALTER COLUMN profile_picture_url TYPE TEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE instagram_accounts MODIFY profile_picture_url VARCHAR(255) NULL');
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE instagram_accounts ALTER COLUMN profile_picture_url TYPE VARCHAR(255)');
        }
    }
};
