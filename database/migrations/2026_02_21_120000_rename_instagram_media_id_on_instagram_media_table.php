<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_media', function (Blueprint $table) {
            $table->renameColumn('instagram_media_id', 'social_account_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('instagram_media', function (Blueprint $table) {
            $table->renameColumn('social_account_media_id', 'instagram_media_id');
        });
    }
};
