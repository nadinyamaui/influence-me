<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_accounts', function (\Illuminate\Database\Schema\Blueprint $table): void {
            $table->text('profile_picture_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instagram_accounts', function (\Illuminate\Database\Schema\Blueprint $table): void {
            $table->string('profile_picture_url', 255)->nullable()->change();
        });
    }
};
