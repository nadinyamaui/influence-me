<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audience_demographics', function (Blueprint $table) {
            $table->dropColumn('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::table('audience_demographics', function (Blueprint $table) {
            $table->timestamp('recorded_at')->nullable(false);
            $table->index(['instagram_account_id', 'type', 'recorded_at'], 'audience_demographics_account_type_recorded_at_index');
        });
    }
};
