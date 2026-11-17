<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductCategory: string
{
    case FinishedGood = 'finished_good';
    case RawMaterial = 'raw_material';

    public function label(): string
    {
        return match ($this) {
            self::FinishedGood => 'Barang Jadi',
            self::RawMaterial => 'Bahan Baku',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::FinishedGood => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::RawMaterial => 'bg-stone-100 text-stone-800 border-stone-200',
        };
    }
}
