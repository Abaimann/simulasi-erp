<?php

declare(strict_types=1);

namespace App\Enums;

enum LotSourceType: string
{
    case OpeningBalance = 'opening_balance';
    case Production = 'production';
    case Procurement = 'procurement';

    public function label(): string
    {
        return match ($this) {
            self::OpeningBalance => 'Saldo Awal',
            self::Production => 'Produksi',
            self::Procurement => 'Pembelian',
        };
    }
}
