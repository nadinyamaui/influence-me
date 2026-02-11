<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table): void {
            $table->string('account_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table): void {
            $table->string('account_type')->nullable(false)->change();
        });
    }
};
