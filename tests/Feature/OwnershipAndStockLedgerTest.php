<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CompanyCode;
use App\Enums\StockStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Company;
use App\Models\Lot;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use App\Services\StockLedgerService;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\FoundationMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OwnershipAndStockLedgerTest extends TestCase
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

    public function test_seeded_initial_stock_separates_owner_company_from_warehouse_manager(): void
    {
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();
        $logistik = Company::query()->where('code', CompanyCode::Logistik->value)->firstOrFail();
        $kotak = Product::query()->where('sku', 'FG-KKT-001')->firstOrFail();
        $gudangCikarang = Warehouse::query()->where('code', 'GDG-CKR-01')->firstOrFail();

        $this->assertSame($logistik->id, $gudangCikarang->company_id);

        $balance = StockBalance::query()
            ->where('product_id', $kotak->id)
            ->where('warehouse_id', $gudangCikarang->id)
            ->firstOrFail();

        $this->assertSame($distribusi->id, $balance->owner_company_id);
        $this->assertSame(StockStatus::Available, $balance->status);
        $this->assertSame('300.000', (string) $balance->quantity);

        // PT Logistik owns ZERO boxes of FG-KKT-001 despite storing 300 in Cikarang
        $logistikOwnedSum = StockBalance::query()
            ->where('product_id', $kotak->id)
            ->where('owner_company_id', $logistik->id)
            ->sum('quantity');

        $this->assertSame(0.0, (float) $logistikOwnedSum);

        // Sales Distribusi sees the 300 boxes owned by PT Distribusi at Cikarang
        $sales = User::query()->where('email', 'sales.distribusi@simulasi-erp.local')->firstOrFail();
        $this->actingAs($sales)
            ->get(route('inventory.balances.index'))
            ->assertOk()
            ->assertSee('300 kotak')
            ->assertSee('PT Reka Distribusi Indonesia')
            ->assertSee('Gudang Utama Logistik Cikarang');
    }

    public function test_logistics_staff_can_transfer_racks_without_changing_legal_ownership(): void
    {
        $logistikUser = User::query()->where('email', 'logistik@simulasi-erp.local')->firstOrFail();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();
        $logistik = Company::query()->where('code', CompanyCode::Logistik->value)->firstOrFail();
        $kotak = Product::query()->where('sku', 'FG-KKT-001')->firstOrFail();
        $lot = Lot::query()->where('lot_number', 'LOT-KKT-AWAL-001')->firstOrFail();
        $gudangCikarang = Warehouse::query()->where('code', 'GDG-CKR-01')->firstOrFail();
        $rackA01 = WarehouseRack::query()->where('warehouse_id', $gudangCikarang->id)->where('code', 'RAK-A01')->firstOrFail();
        $rackA02 = WarehouseRack::query()->where('warehouse_id', $gudangCikarang->id)->where('code', 'RAK-A02')->firstOrFail();

        $response = $this->actingAs($logistikUser)->post(route('inventory.movements.rack-transfer'), [
            'product_id' => $kotak->id,
            'lot_id' => $lot->id,
            'owner_company_id' => $distribusi->id,
            'warehouse_id' => $gudangCikarang->id,
            'from_rack_id' => $rackA01->id,
            'to_rack_id' => $rackA02->id,
            'status' => StockStatus::Available->value,
            'quantity' => 120,
            'notes' => 'Pemindahan rak internal Cikarang',
            'idempotency_key' => 'test-rack-transfer-001',
        ]);

        $response->assertRedirect(route('inventory.movements.index'));

        $balanceA01 = StockBalance::query()->where('rack_id', $rackA01->id)->where('owner_company_id', $distribusi->id)->firstOrFail();
        $balanceA02 = StockBalance::query()->where('rack_id', $rackA02->id)->where('owner_company_id', $distribusi->id)->firstOrFail();

        $this->assertSame('180.000', (string) $balanceA01->quantity);
        $this->assertSame('120.000', (string) $balanceA02->quantity);

        // PT Logistik still owns zero boxes
        $this->assertDatabaseMissing('stock_balances', [
            'product_id' => $kotak->id,
            'owner_company_id' => $logistik->id,
        ]);

        // Re-running with same idempotency_key does not double-move stock
        $ledgerService = app(StockLedgerService::class);
        $ledgerService->transferBetweenRacks(
            product: $kotak,
            lot: $lot,
            ownerCompany: $distribusi,
            warehouse: $gudangCikarang,
            fromRack: $rackA01,
            toRack: $rackA02,
            status: StockStatus::Available,
            quantity: 120,
            actor: $logistikUser,
            performedCompany: $logistik,
            idempotencyKey: 'test-rack-transfer-001',
        );

        $this->assertSame(1, StockMovement::query()->where('idempotency_key', 'test-rack-transfer-001')->count());
        $this->assertSame('180.000', (string) $balanceA01->fresh()->quantity);
    }

    public function test_transfer_exceeding_available_balance_is_rejected(): void
    {
        $logistikUser = User::query()->where('email', 'logistik@simulasi-erp.local')->firstOrFail();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();
        $logistik = Company::query()->where('code', CompanyCode::Logistik->value)->firstOrFail();
        $kotak = Product::query()->where('sku', 'FG-KKT-001')->firstOrFail();
        $lot = Lot::query()->where('lot_number', 'LOT-KKT-AWAL-001')->firstOrFail();
        $gudangCikarang = Warehouse::query()->where('code', 'GDG-CKR-01')->firstOrFail();
        $rackA01 = WarehouseRack::query()->where('warehouse_id', $gudangCikarang->id)->where('code', 'RAK-A01')->firstOrFail();
        $rackA02 = WarehouseRack::query()->where('warehouse_id', $gudangCikarang->id)->where('code', 'RAK-A02')->firstOrFail();

        $this->expectException(InsufficientStockException::class);

        app(StockLedgerService::class)->transferBetweenRacks(
            product: $kotak,
            lot: $lot,
            ownerCompany: $distribusi,
            warehouse: $gudangCikarang,
            fromRack: $rackA01,
            toRack: $rackA02,
            status: StockStatus::Available,
            quantity: 500,
            actor: $logistikUser,
            performedCompany: $logistik,
        );
    }
}
