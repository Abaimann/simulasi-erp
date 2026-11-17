<?php

declare(strict_types=1);

namespace App\Enums;

enum StockStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case InTransit = 'in_transit';
    case Quarantine = 'quarantine';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Reserved => 'Direservasi',
            self::InTransit => 'Dalam Perjalanan',
            self::Quarantine => 'Karantina',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Available => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::Reserved => 'bg-amber-100 text-amber-800 border-amber-200',
            self::InTransit => 'bg-sky-100 text-sky-800 border-sky-200',
            self::Quarantine => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }
}
