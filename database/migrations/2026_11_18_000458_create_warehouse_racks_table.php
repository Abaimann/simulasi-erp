<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_racks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 120);
            $table->string('zone_type', 32);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['warehouse_id', 'code'], 'warehouse_racks_warehouse_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_racks');
    }
};
