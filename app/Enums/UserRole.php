<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case AdminGrup = 'admin_grup';
    case SalesDistribusi = 'sales_distribusi';
    case Purchasing = 'purchasing';
    case SupervisorProduksi = 'supervisor_produksi';
    case PetugasQc = 'petugas_qc';
    case PetugasLogistik = 'petugas_logistik';
    case Finance = 'finance';
    case ManajemenGrup = 'manajemen_grup';

    public function label(): string
    {
        return match ($this) {
            self::AdminGrup => 'Admin Grup',
            self::SalesDistribusi => 'Sales Distribusi',
            self::Purchasing => 'Purchasing',
            self::SupervisorProduksi => 'Supervisor Produksi',
            self::PetugasQc => 'Petugas QC',
            self::PetugasLogistik => 'Petugas Gudang dan Logistik',
            self::Finance => 'Finance',
            self::ManajemenGrup => 'Manajemen Grup',
        };
    }

    public function authoritySummary(): string
    {
        return match ($this) {
            self::AdminGrup => 'Mengelola perusahaan, pengguna, keanggotaan, dan role tanpa memposting transaksi.',
            self::SalesDistribusi => 'Membuat pesanan pelanggan dan memantau stok serta pengiriman PT Distribusi.',
            self::Purchasing => 'Membuat PO untuk PT tempat pengguna ditugaskan.',
            self::SupervisorProduksi => 'Menyetujui dan menjalankan perintah produksi.',
            self::PetugasQc => 'Mencatat hasil lulus atau gagal per lot produksi.',
            self::PetugasLogistik => 'Mengelola penerimaan, rak, picking, dan pengiriman tanpa melihat harga jual pelanggan.',
            self::Finance => 'Memeriksa dan memposting invoice atau tagihan di PT masing-masing.',
            self::ManajemenGrup => 'Melihat ringkasan lintas PT secara baca-saja tanpa mengubah transaksi operasional.',
        };
    }

    /**
     * Check whether this role is valid when assigned to a company of the given business type.
     */
    public function isAllowedForCompanyType(CompanyBusinessType $businessType): bool
    {
        return match ($this) {
            self::AdminGrup, self::ManajemenGrup, self::Purchasing, self::Finance => true,
            self::SalesDistribusi => $businessType === CompanyBusinessType::Distribution,
            self::SupervisorProduksi, self::PetugasQc => $businessType === CompanyBusinessType::Manufacturing,
            self::PetugasLogistik => $businessType === CompanyBusinessType::Logistics,
        };
    }

    /**
     * Only Sales/Finance at PT Distribusi or Manajemen Grup may view customer selling prices.
     */
    public function canViewCustomerPrice(CompanyCode $activeCompanyCode): bool
    {
        if ($this === self::ManajemenGrup) {
            return true;
        }

        if ($activeCompanyCode !== CompanyCode::Distribusi) {
            return false;
        }

        return in_array($this, [self::SalesDistribusi, self::Finance], true);
    }

    public function canManageGroupMaster(): bool
    {
        return $this === self::AdminGrup;
    }

    public function canViewGroupSummary(): bool
    {
        return $this === self::ManajemenGrup;
    }

    public function canManageWarehouseRacks(): bool
    {
        return in_array($this, [self::PetugasLogistik, self::AdminGrup], true);
    }

    public function canRecordRackTransfer(): bool
    {
        return $this === self::PetugasLogistik;
    }
}
