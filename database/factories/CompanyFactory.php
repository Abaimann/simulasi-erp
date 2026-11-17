<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CompanyBusinessType;
use App\Enums\CompanyCode;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => CompanyCode::Distribusi,
            'name' => 'PT Reka Distribusi Indonesia',
            'short_name' => 'PT Distribusi',
            'business_type' => CompanyBusinessType::Distribution,
            'city' => 'Bekasi',
            'address' => 'Kawasan Niaga Bekasi',
            'description' => 'Perusahaan distribusi grup simulasi.',
            'is_active' => true,
        ];
    }

    public function produksi(): static
    {
        return $this->state([
            'code' => CompanyCode::Produksi,
            'name' => 'PT Reka Kemasan Nusantara',
            'short_name' => 'PT Produksi',
            'business_type' => CompanyBusinessType::Manufacturing,
            'city' => 'Karawang',
            'address' => 'Kawasan Industri Karawang Barat',
        ]);
    }

    public function distribusi(): static
    {
        return $this->state([
            'code' => CompanyCode::Distribusi,
            'name' => 'PT Reka Distribusi Indonesia',
            'short_name' => 'PT Distribusi',
            'business_type' => CompanyBusinessType::Distribution,
            'city' => 'Bekasi',
            'address' => 'Kawasan Bisnis Bekasi Selatan',
        ]);
    }

    public function logistik(): static
    {
        return $this->state([
            'code' => CompanyCode::Logistik,
            'name' => 'PT Reka Logistik Indonesia',
            'short_name' => 'PT Logistik',
            'business_type' => CompanyBusinessType::Logistics,
            'city' => 'Cikarang',
            'address' => 'Kawasan Pergudangan Cikarang Pusat',
        ]);
    }
}
