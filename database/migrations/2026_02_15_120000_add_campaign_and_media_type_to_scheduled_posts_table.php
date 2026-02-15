<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table): void {
            $table->foreignId('campaign_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->string('media_type')->default('post')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropColumn('media_type');
        });
    }
};
