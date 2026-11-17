<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table): void {
            $table->id();
            $table->string('bucket_key', 160)->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('lot_id')->constrained('lots')->restrictOnDelete();
            $table->foreignId('owner_company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks')->restrictOnDelete();
            $table->string('status', 32);
            $table->decimal('quantity', 15, 3)->default(0);
            $table->timestamps();

            $table->index(['owner_company_id', 'status']);
            $table->index(['warehouse_id', 'rack_id', 'status']);
            $table->index(['product_id', 'owner_company_id', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE stock_balances ADD CONSTRAINT stock_balances_quantity_non_negative CHECK (quantity >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
