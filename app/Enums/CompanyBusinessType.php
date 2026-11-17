<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanyBusinessType: string
{
    case Manufacturing = 'manufacturing';
    case Distribution = 'distribution';
    case Logistics = 'logistics';

    public function label(): string
    {
        return match ($this) {
            self::Manufacturing => 'Manufaktur & Produksi',
            self::Distribution => 'Distribusi & Penjualan',
            self::Logistics => 'Gudang & Logistik',
        };
    }
}
