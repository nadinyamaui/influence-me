<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('platform');
            $table->string('media_type')->nullable();
            $table->string('billing_unit');
            $table->decimal('base_price', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('catalog_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('bundle_price', 10, 2)->nullable();
            $table->char('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('catalog_plan_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalog_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_price_override', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('proposal_line_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('name_snapshot');
            $table->text('description_snapshot')->nullable();
            $table->string('platform_snapshot')->nullable();
            $table->string('media_type_snapshot')->nullable();
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_line_items');
        Schema::dropIfExists('catalog_plan_items');
        Schema::dropIfExists('catalog_plans');
        Schema::dropIfExists('catalog_products');
    }
};
