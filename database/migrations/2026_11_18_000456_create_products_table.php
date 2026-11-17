<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->string('category', 32);
            $table->string('unit', 32);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('intercompany_price')->nullable();
            $table->unsignedBigInteger('customer_selling_price')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_product_access', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'product_id'], 'company_product_access_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_product_access');
        Schema::dropIfExists('products');
    }
};
