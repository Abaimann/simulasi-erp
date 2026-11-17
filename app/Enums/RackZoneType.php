<?php

declare(strict_types=1);

namespace App\Enums;

enum RackZoneType: string
{
    case Storage = 'storage';
    case Receiving = 'receiving';
    case Staging = 'staging';
    case Quarantine = 'quarantine';

    public function label(): string
    {
        return match ($this) {
            self::Storage => 'Penyimpanan',
            self::Receiving => 'Penerimaan',
            self::Staging => 'Staging / Picking',
            self::Quarantine => 'Karantina QC',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Storage => 'bg-slate-100 text-slate-800 border-slate-200',
            self::Receiving => 'bg-sky-100 text-sky-800 border-sky-200',
            self::Staging => 'bg-violet-100 text-violet-800 border-violet-200',
            self::Quarantine => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }
}
