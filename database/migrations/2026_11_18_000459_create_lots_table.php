<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('origin_company_id')->constrained('companies')->restrictOnDelete();
            $table->string('lot_number', 64)->unique();
            $table->string('source_type', 32);
            $table->timestamp('manufactured_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'origin_company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
