<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('role', 40);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'company_id', 'role'], 'company_memberships_user_company_role_unique');
            $table->index(['company_id', 'role', 'is_active'], 'company_memberships_scope_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_memberships');
    }
};
