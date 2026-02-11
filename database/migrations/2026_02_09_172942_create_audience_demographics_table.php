<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audience_demographics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('dimension');
            $table->decimal('value', 5, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['instagram_account_id', 'type', 'recorded_at'], 'audience_demographics_account_type_recorded_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audience_demographics');
    }
};
