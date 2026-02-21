<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->foreignId('catalog_product_id')
                ->nullable()
                ->after('invoice_id')
                ->constrained('catalog_products')
                ->nullOnDelete();
            $table->foreignId('catalog_plan_id')
                ->nullable()
                ->after('catalog_product_id')
                ->constrained('catalog_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('catalog_plan_id');
            $table->dropConstrainedForeignId('catalog_product_id');
        });
    }
};
