<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompanyCode;
use App\Http\Middleware\EnsureActiveCompany;
use App\Models\Company;
use App\Models\Lot;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\FoundationMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CrossCompanyAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CompanySeeder::class,
            DemoUserSeeder::class,
            FoundationMasterSeeder::class,
        ]);
    }

    public function test_logistics_staff_cannot_access_customer_pricing_via_direct_url_or_product_views(): void
    {
        $logistikUser = User::query()->where('email', 'logistik@simulasi-erp.local')->firstOrFail();
        $kotakKarton = Product::query()->where('sku', 'FG-KKT-001')->firstOrFail();

        $this->actingAs($logistikUser)
            ->get(route('master.pricing.index'))
            ->assertForbidden();

        $productIndex = $this->actingAs($logistikUser)->get(route('master.products.index'));
        $productIndex->assertOk();
        $productIndex->assertSee('Kotak Karton Standar');
        $productIndex->assertDontSee('Rp7.000');
        $productIndex->assertDontSee('Harga Jual Pelanggan');

        $productDetail = $this->actingAs($logistikUser)->get(route('master.products.show', $kotakKarton));
        $productDetail->assertOk();
        $productDetail->assertDontSee('Rp7.000');
        $productDetail->assertDontSee('Harga Jual Pelanggan');
    }

    public function test_authorized_distribusi_and_group_management_can_view_customer_pricing(): void
    {
        $sales = User::query()->where('email', 'sales.distribusi@simulasi-erp.local')->firstOrFail();
        $financeDist = User::query()->where('email', 'finance.distribusi@simulasi-erp.local')->firstOrFail();
        $manajemen = User::query()->where('email', 'manajemen@simulasi-erp.local')->firstOrFail();
        $financeLogistik = User::query()->where('email', 'finance.logistik@simulasi-erp.local')->firstOrFail();

        $this->actingAs($sales)->get(route('master.pricing.index'))->assertOk()->assertSee('Rp7.000');
        $this->actingAs($financeDist)->get(route('master.pricing.index'))->assertOk()->assertSee('Rp7.000');
        $this->actingAs($manajemen)->get(route('master.pricing.index'))->assertOk()->assertSee('Rp7.000');
        $this->actingAs($financeLogistik)->get(route('master.pricing.index'))->assertForbidden();
    }

    public function test_direct_url_access_to_other_company_products_warehouses_and_lots_is_forbidden(): void
    {
        $sales = User::query()->where('email', 'sales.distribusi@simulasi-erp.local')->firstOrFail();
        $logistikUser = User::query()->where('email', 'logistik@simulasi-erp.local')->firstOrFail();
        $supervisor = User::query()->where('email', 'supervisor.produksi@simulasi-erp.local')->firstOrFail();

        $lemIndustri = Product::query()->where('sku', 'RM-LEM-001')->firstOrFail();
        $gudangCikarang = Warehouse::query()->where('code', 'GDG-CKR-01')->firstOrFail();
        $gudangKarawang = Warehouse::query()->where('code', 'GDG-KRW-01')->firstOrFail();
        $lotLem = Lot::query()->where('lot_number', 'LOT-LEM-AWAL-001')->firstOrFail();
        $lotKotakDistribusi = Lot::query()->where('lot_number', 'LOT-KKT-AWAL-001')->firstOrFail();

        // Sales Distribusi cannot view Produksi raw material, Cikarang/Karawang warehouse management, or Produksi lot
        $this->actingAs($sales)->get(route('master.products.show', $lemIndustri))->assertForbidden();
        $this->actingAs($sales)->get(route('master.warehouses.show', $gudangCikarang))->assertForbidden();
        $this->actingAs($sales)->get(route('master.warehouses.show', $gudangKarawang))->assertForbidden();
        $this->actingAs($sales)->get(route('inventory.lots.show', $lotLem))->assertForbidden();

        // Sales Distribusi cannot create racks in Cikarang warehouse
        $this->actingAs($sales)->post(route('master.warehouses.racks.store', $gudangCikarang), [
            'code' => 'RAK-HACK',
            'name' => 'Rak Ilegal',
            'zone_type' => 'storage',
        ])->assertForbidden();

        // Petugas Logistik cannot view Produksi raw material, Karawang warehouse, or Produksi lot
        $this->actingAs($logistikUser)->get(route('master.products.show', $lemIndustri))->assertForbidden();
        $this->actingAs($logistikUser)->get(route('master.warehouses.show', $gudangKarawang))->assertForbidden();
        $this->actingAs($logistikUser)->get(route('inventory.lots.show', $lotLem))->assertForbidden();

        // Supervisor Produksi cannot view Distribusi-owned lot stored in Cikarang
        $this->actingAs($supervisor)->get(route('inventory.lots.show', $lotKotakDistribusi))->assertForbidden();
    }

    public function test_admin_grup_and_manajemen_grup_cannot_post_operational_stock_movements(): void
    {
        $admin = User::query()->where('email', 'admin@simulasi-erp.local')->firstOrFail();
        $manajemen = User::query()->where('email', 'manajemen@simulasi-erp.local')->firstOrFail();
        $logistik = Company::query()->where('code', CompanyCode::Logistik->value)->firstOrFail();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();
        $kotakKarton = Product::query()->where('sku', 'FG-KKT-001')->firstOrFail();
        $lot = Lot::query()->where('lot_number', 'LOT-KKT-AWAL-001')->firstOrFail();
        $warehouse = Warehouse::query()->where('code', 'GDG-CKR-01')->firstOrFail();
        $fromRack = WarehouseRack::query()->where('warehouse_id', $warehouse->id)->where('code', 'RAK-A01')->firstOrFail();
        $toRack = WarehouseRack::query()->where('warehouse_id', $warehouse->id)->where('code', 'RAK-A02')->firstOrFail();

        $payload = [
            'product_id' => $kotakKarton->id,
            'lot_id' => $lot->id,
            'owner_company_id' => $distribusi->id,
            'warehouse_id' => $warehouse->id,
            'from_rack_id' => $fromRack->id,
            'to_rack_id' => $toRack->id,
            'status' => 'available',
            'quantity' => 50,
        ];

        $this->actingAs($admin)
            ->withSession([EnsureActiveCompany::SESSION_KEY => $logistik->id])
            ->post(route('inventory.movements.rack-transfer'), $payload)
            ->assertForbidden();

        $this->actingAs($manajemen)
            ->withSession([EnsureActiveCompany::SESSION_KEY => $logistik->id])
            ->post(route('inventory.movements.rack-transfer'), $payload)
            ->assertForbidden();
    }

    public function test_operational_users_cannot_access_admin_or_group_summary_routes(): void
    {
        $sales = User::query()->where('email', 'sales.distribusi@simulasi-erp.local')->firstOrFail();
        $admin = User::query()->where('email', 'admin@simulasi-erp.local')->firstOrFail();

        $this->actingAs($sales)->get(route('admin.companies.index'))->assertForbidden();
        $this->actingAs($sales)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($sales)->get(route('group.summary.index'))->assertForbidden();

        // Admin Grup cannot open Manajemen Grup cross-PT summary unless assigned Manajemen Grup
        $this->actingAs($admin)->get(route('group.summary.index'))->assertForbidden();
    }
}
