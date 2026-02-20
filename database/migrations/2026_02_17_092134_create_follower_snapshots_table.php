<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follower_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('social_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('followers_count');
            $table->date('recorded_at');
            $table->timestamps();

            $table->unique(['social_account_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follower_snapshots');
    }
};
