<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('action', 80);
            $table->string('document_type', 80)->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('document_number', 80)->nullable();
            $table->text('description');
            $table->jsonb('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['company_id', 'occurred_at']);
            $table->index(['action', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
