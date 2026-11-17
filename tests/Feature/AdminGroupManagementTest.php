<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompanyCode;
use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminGroupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CompanySeeder::class, DemoUserSeeder::class]);
    }

    public function test_admin_grup_can_create_user_and_assign_compatible_membership(): void
    {
        $admin = User::query()->where('email', 'admin@simulasi-erp.local')->firstOrFail();
        $produksi = Company::query()->where('code', CompanyCode::Produksi->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Operator Baru',
            'email' => 'operator.baru@simulasi-erp.local',
            'password' => 'password123',
            'company_id' => $produksi->id,
            'role' => UserRole::SupervisorProduksi->value,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'operator.baru@simulasi-erp.local']);
        $this->assertDatabaseHas('company_memberships', [
            'company_id' => $produksi->id,
            'role' => UserRole::SupervisorProduksi->value,
        ]);
    }

    public function test_incompatible_role_for_company_type_is_rejected(): void
    {
        $admin = User::query()->where('email', 'admin@simulasi-erp.local')->firstOrFail();
        $produksi = Company::query()->where('code', CompanyCode::Produksi->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Salah Role',
            'email' => 'salah.role@simulasi-erp.local',
            'password' => 'password123',
            'company_id' => $produksi->id,
            'role' => UserRole::SalesDistribusi->value,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'salah.role@simulasi-erp.local']);
    }

    public function test_admin_grup_can_create_master_product_with_company_scope(): void
    {
        $admin = User::query()->where('email', 'admin@simulasi-erp.local')->firstOrFail();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('master.products.store'), [
            'sku' => 'FG-KKT-002',
            'name' => 'Kotak Karton Besar',
            'category' => ProductCategory::FinishedGood->value,
            'unit' => 'kotak',
            'intercompany_price' => 8000,
            'customer_selling_price' => 11000,
            'company_ids' => [$distribusi->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'sku' => 'FG-KKT-002',
            'name' => 'Kotak Karton Besar',
        ]);
    }
}
