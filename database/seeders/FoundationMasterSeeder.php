<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyCode;
use App\Enums\LotSourceType;
use App\Enums\ProductCategory;
use App\Enums\RackZoneType;
use App\Models\Company;
use App\Models\Lot;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRack;
use App\Services\StockLedgerService;
use Illuminate\Database\Seeder;

class FoundationMasterSeeder extends Seeder
{
    public function run(StockLedgerService $stockLedgerService): void
    {
        $produksi = Company::query()->where('code', CompanyCode::Produksi->value)->firstOrFail();
        $distribusi = Company::query()->where('code', CompanyCode::Distribusi->value)->firstOrFail();
        $logistik = Company::query()->where('code', CompanyCode::Logistik->value)->firstOrFail();

        $actor = User::query()->where('email', 'admin@simulasi-erp.local')->first()
            ?? User::query()->firstOrFail();

        $kotakKarton = Product::query()->updateOrCreate(
            ['sku' => 'FG-KKT-001'],
            [
                'name' => 'Kotak Karton Standar',
                'category' => ProductCategory::FinishedGood,
                'unit' => 'kotak',
                'description' => 'Produk jadi kotak karton bergelombang ukuran standar.',
                'intercompany_price' => 5000,
                'customer_selling_price' => 7000,
                'is_active' => true,
            ]
        );
        $kotakKarton->accessibleCompanies()->sync([$produksi->id, $distribusi->id, $logistik->id]);

        $lembaranKarton = Product::query()->updateOrCreate(
            ['sku' => 'RM-KRT-001'],
            [
                'name' => 'Lembaran Karton',
                'category' => ProductCategory::RawMaterial,
                'unit' => 'lembar',
                'description' => 'Bahan baku utama lembaran karton untuk produksi kotak.',
                'intercompany_price' => null,
                'customer_selling_price' => null,
                'is_active' => true,
            ]
        );
        $lembaranKarton->accessibleCompanies()->sync([$produksi->id]);

        $lemIndustri = Product::query()->updateOrCreate(
            ['sku' => 'RM-LEM-001'],
            [
                'name' => 'Lem Industri',
                'category' => ProductCategory::RawMaterial,
                'unit' => 'kg',
                'description' => 'Bahan baku perekat industri (kebutuhan BOM 10 gram = 0,010 kg per kotak).',
                'intercompany_price' => null,
                'customer_selling_price' => null,
                'is_active' => true,
            ]
        );
        $lemIndustri->accessibleCompanies()->sync([$produksi->id]);

        $gudangKarawang = Warehouse::query()->updateOrCreate(
            ['code' => 'GDG-KRW-01'],
            [
                'company_id' => $produksi->id,
                'name' => 'Gudang Bahan & Produksi Karawang',
                'city' => 'Karawang',
                'address' => 'Kawasan Industri Karawang Barat Blok C-12',
                'is_third_party_storage' => false,
                'is_active' => true,
            ]
        );

        $gudangBekasi = Warehouse::query()->updateOrCreate(
            ['code' => 'GDG-BKS-01'],
            [
                'company_id' => $distribusi->id,
                'name' => 'Gudang Transit Distribusi Bekasi',
                'city' => 'Bekasi',
                'address' => 'Jl. Niaga Raya No. 45, Bekasi',
                'is_third_party_storage' => false,
                'is_active' => true,
            ]
        );

        $gudangCikarang = Warehouse::query()->updateOrCreate(
            ['code' => 'GDG-CKR-01'],
            [
                'company_id' => $logistik->id,
                'name' => 'Gudang Utama Logistik Cikarang',
                'city' => 'Cikarang',
                'address' => 'Kawasan Pergudangan Cikarang Pusat Kav. 8',
                'is_third_party_storage' => true,
                'is_active' => true,
            ]
        );

        $rakBahanKarawang = WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangKarawang->id, 'code' => 'RAK-BHN-01'],
            ['name' => 'Rak Bahan Baku A', 'zone_type' => RackZoneType::Storage, 'is_active' => true]
        );
        WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangKarawang->id, 'code' => 'RAK-FG-01'],
            ['name' => 'Rak Barang Jadi Siap Kirim', 'zone_type' => RackZoneType::Staging, 'is_active' => true]
        );
        WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangKarawang->id, 'code' => 'RAK-KRN-01'],
            ['name' => 'Area Karantina QC Karawang', 'zone_type' => RackZoneType::Quarantine, 'is_active' => true]
        );

        WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangBekasi->id, 'code' => 'RAK-DST-01'],
            ['name' => 'Rak Staging Distribusi', 'zone_type' => RackZoneType::Staging, 'is_active' => true]
        );

        $rakCikarangA01 = WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangCikarang->id, 'code' => 'RAK-A01'],
            ['name' => 'Rak Penyimpanan Utama A-01', 'zone_type' => RackZoneType::Storage, 'is_active' => true]
        );
        WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangCikarang->id, 'code' => 'RAK-A02'],
            ['name' => 'Rak Penyimpanan Utama A-02', 'zone_type' => RackZoneType::Storage, 'is_active' => true]
        );
        WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangCikarang->id, 'code' => 'RAK-RCV-01'],
            ['name' => 'Area Penerimaan Barang', 'zone_type' => RackZoneType::Receiving, 'is_active' => true]
        );
        WarehouseRack::query()->updateOrCreate(
            ['warehouse_id' => $gudangCikarang->id, 'code' => 'RAK-PCK-01'],
            ['name' => 'Area Picking & Pengiriman', 'zone_type' => RackZoneType::Staging, 'is_active' => true]
        );

        $lotKotakAwal = Lot::query()->updateOrCreate(
            ['lot_number' => 'LOT-KKT-AWAL-001'],
            [
                'product_id' => $kotakKarton->id,
                'origin_company_id' => $distribusi->id,
                'source_type' => LotSourceType::OpeningBalance,
                'manufactured_at' => now()->subDays(5),
                'notes' => 'Stok awal 300 kotak milik PT Distribusi yang dititipkan di gudang PT Logistik Cikarang.',
            ]
        );

        $lotKartonAwal = Lot::query()->updateOrCreate(
            ['lot_number' => 'LOT-KRT-AWAL-001'],
            [
                'product_id' => $lembaranKarton->id,
                'origin_company_id' => $produksi->id,
                'source_type' => LotSourceType::OpeningBalance,
                'manufactured_at' => now()->subDays(7),
                'notes' => 'Stok awal bahan baku lembaran karton di gudang Karawang.',
            ]
        );

        $lotLemAwal = Lot::query()->updateOrCreate(
            ['lot_number' => 'LOT-LEM-AWAL-001'],
            [
                'product_id' => $lemIndustri->id,
                'origin_company_id' => $produksi->id,
                'source_type' => LotSourceType::OpeningBalance,
                'manufactured_at' => now()->subDays(7),
                'notes' => 'Stok awal bahan baku lem industri di gudang Karawang.',
            ]
        );

        $stockLedgerService->recordOpeningBalance(
            product: $kotakKarton,
            lot: $lotKotakAwal,
            ownerCompany: $distribusi,
            warehouse: $gudangCikarang,
            rack: $rakCikarangA01,
            quantity: 300,
            actor: $actor,
            performedCompany: $distribusi,
            referenceNumber: 'INIT-STOK-DIST-001',
            notes: 'Saldo awal 300 kotak milik PT Reka Distribusi Indonesia di Gudang Utama Logistik Cikarang.',
            idempotencyKey: 'seed-opening-kkt-distribusi-001',
            occurredAt: now()->subDays(5),
        );

        $stockLedgerService->recordOpeningBalance(
            product: $lembaranKarton,
            lot: $lotKartonAwal,
            ownerCompany: $produksi,
            warehouse: $gudangKarawang,
            rack: $rakBahanKarawang,
            quantity: 800,
            actor: $actor,
            performedCompany: $produksi,
            referenceNumber: 'INIT-STOK-PROD-001',
            notes: 'Saldo awal 800 lembar karton milik PT Reka Kemasan Nusantara di Gudang Karawang.',
            idempotencyKey: 'seed-opening-krt-produksi-001',
            occurredAt: now()->subDays(7),
        );

        $stockLedgerService->recordOpeningBalance(
            product: $lemIndustri,
            lot: $lotLemAwal,
            ownerCompany: $produksi,
            warehouse: $gudangKarawang,
            rack: $rakBahanKarawang,
            quantity: 5,
            actor: $actor,
            performedCompany: $produksi,
            referenceNumber: 'INIT-STOK-PROD-002',
            notes: 'Saldo awal 5 kg lem industri milik PT Reka Kemasan Nusantara di Gudang Karawang.',
            idempotencyKey: 'seed-opening-lem-produksi-001',
            occurredAt: now()->subDays(7),
        );
    }
}
