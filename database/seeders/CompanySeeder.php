<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CompanyBusinessType;
use App\Enums\CompanyCode;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'code' => CompanyCode::Produksi->value,
                'name' => 'PT Reka Kemasan Nusantara',
                'short_name' => 'PT Produksi',
                'business_type' => CompanyBusinessType::Manufacturing->value,
                'city' => 'Karawang',
                'address' => 'Kawasan Industri Karawang Barat Blok C-12, Karawang',
                'description' => 'Pembelian bahan baku, produksi kotak karton, QC, dan penjualan antar-PT ke PT Distribusi.',
                'is_active' => true,
            ],
            [
                'code' => CompanyCode::Distribusi->value,
                'name' => 'PT Reka Distribusi Indonesia',
                'short_name' => 'PT Distribusi',
                'business_type' => CompanyBusinessType::Distribution->value,
                'city' => 'Bekasi',
                'address' => 'Jl. Niaga Raya No. 45, Bekasi',
                'description' => 'Penjualan ke pelanggan, reservasi stok, pembelian antar-PT, alokasi stok, dan invoice pelanggan.',
                'is_active' => true,
            ],
            [
                'code' => CompanyCode::Logistik->value,
                'name' => 'PT Reka Logistik Indonesia',
                'short_name' => 'PT Logistik',
                'business_type' => CompanyBusinessType::Logistics->value,
                'city' => 'Cikarang',
                'address' => 'Kawasan Pergudangan Cikarang Pusat Kav. 8, Cikarang',
                'description' => 'Gudang titipan, penerimaan barang, manajemen rak, picking, pengiriman, bukti serah terima, dan tagihan jasa.',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $companyData) {
            Company::query()->updateOrCreate(
                ['code' => $companyData['code']],
                $companyData
            );
        }
    }
}
