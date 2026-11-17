<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanyCode: string
{
    case Produksi = 'PRODUKSI';
    case Distribusi = 'DISTRIBUSI';
    case Logistik = 'LOGISTIK';

    public function label(): string
    {
        return match ($this) {
            self::Produksi => 'PT Reka Kemasan Nusantara',
            self::Distribusi => 'PT Reka Distribusi Indonesia',
            self::Logistik => 'PT Reka Logistik Indonesia',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Produksi => 'PT Produksi',
            self::Distribusi => 'PT Distribusi',
            self::Logistik => 'PT Logistik',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Produksi => 'bg-amber-100 text-amber-900 border-amber-300',
            self::Distribusi => 'bg-blue-100 text-blue-900 border-blue-300',
            self::Logistik => 'bg-emerald-100 text-emerald-900 border-emerald-300',
        };
    }

    public function headerClasses(): string
    {
        return match ($this) {
            self::Produksi => 'bg-amber-50 border-amber-300 text-amber-950',
            self::Distribusi => 'bg-blue-50 border-blue-300 text-blue-950',
            self::Logistik => 'bg-emerald-50 border-emerald-300 text-emerald-950',
        };
    }
}
