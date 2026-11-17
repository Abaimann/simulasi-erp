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
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->string('movement_number', 64)->unique();
            $table->string('idempotency_key', 120)->nullable()->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('lot_id')->constrained('lots')->restrictOnDelete();
            $table->string('movement_type', 40);
            $table->foreignId('from_owner_company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->foreignId('to_owner_company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('from_rack_id')->nullable()->constrained('warehouse_racks')->restrictOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('to_rack_id')->nullable()->constrained('warehouse_racks')->restrictOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->decimal('quantity', 15, 3);
            $table->string('reference_type', 60);
            $table->string('reference_number', 80);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('performed_company_id')->constrained('companies')->restrictOnDelete();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'lot_id', 'occurred_at']);
            $table->index(['from_owner_company_id', 'to_owner_company_id'], 'stock_movements_owners_index');
            $table->index(['reference_type', 'reference_number']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_quantity_positive CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
