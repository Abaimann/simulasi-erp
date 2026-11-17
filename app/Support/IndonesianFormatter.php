<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class IndonesianFormatter
{
    public const string TIMEZONE = 'Asia/Jakarta';

    public static function rupiah(int|float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }

        $numericAmount = (float) $amount;

        return 'Rp'.number_format($numericAmount, 0, ',', '.');
    }

    public static function quantity(int|float|string|null $value, ?string $unit = null): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $formatted = number_format((float) $value, 3, ',', '.');

        if (str_contains($formatted, ',')) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, ',');
        }

        if ($unit !== null && $unit !== '') {
            return $formatted.' '.$unit;
        }

        return $formatted;
    }

    public static function wib(CarbonInterface|string|null $dateTime, string $format = 'd M Y H:i'): string
    {
        if ($dateTime === null || $dateTime === '') {
            return '-';
        }

        $carbon = $dateTime instanceof CarbonInterface
            ? $dateTime->copy()
            : Carbon::parse($dateTime);

        return $carbon->timezone(self::TIMEZONE)->translatedFormat($format).' WIB';
    }
}
