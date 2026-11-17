<?php

declare(strict_types=1);

namespace App\Enums;

enum StockMovementType: string
{
    case OpeningBalance = 'opening_balance';
    case RackTransfer = 'rack_transfer';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case Reservation = 'reservation';
    case ReleaseReservation = 'release_reservation';
    case ProductionConsumption = 'production_consumption';
    case ProductionOutput = 'production_output';
    case QcPass = 'qc_pass';
    case QcFail = 'qc_fail';
    case TransitOut = 'transit_out';
    case OwnershipTransferReceipt = 'ownership_transfer_receipt';
    case CustomerDelivery = 'customer_delivery';

    public function label(): string
    {
        return match ($this) {
            self::OpeningBalance => 'Saldo Awal',
            self::RackTransfer => 'Pindah Rak',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::Reservation => 'Reservasi Stok',
            self::ReleaseReservation => 'Lepas Reservasi',
            self::ProductionConsumption => 'Konsumsi Produksi',
            self::ProductionOutput => 'Hasil Produksi',
            self::QcPass => 'Lulus QC',
            self::QcFail => 'Gagal QC (Karantina)',
            self::TransitOut => 'Kirim Surat Jalan',
            self::OwnershipTransferReceipt => 'Penerimaan & Alih Kepemilikan',
            self::CustomerDelivery => 'Pengiriman Pelanggan',
        };
    }
}
