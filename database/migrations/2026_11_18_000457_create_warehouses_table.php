<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('city', 80);
            $table->text('address')->nullable();
            $table->boolean('is_third_party_storage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
