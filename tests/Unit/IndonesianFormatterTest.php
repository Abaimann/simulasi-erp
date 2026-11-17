<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\IndonesianFormatter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

final class IndonesianFormatterTest extends TestCase
{
    public function test_formats_rupiah_correctly(): void
    {
        $this->assertSame('Rp7.000', IndonesianFormatter::rupiah(7000));
        $this->assertSame('Rp3.500.000', IndonesianFormatter::rupiah(3500000));
        $this->assertSame('Rp0', IndonesianFormatter::rupiah(0));
        $this->assertSame('-', IndonesianFormatter::rupiah(null));
    }

    public function test_formats_quantities_with_units_and_decimals(): void
    {
        $this->assertSame('300 kotak', IndonesianFormatter::quantity('300.000', 'kotak'));
        $this->assertSame('1.000 kotak', IndonesianFormatter::quantity(1000, 'kotak'));
        $this->assertSame('5 kg', IndonesianFormatter::quantity('5.000', 'kg'));
        $this->assertSame('0,01 kg', IndonesianFormatter::quantity('0.010', 'kg'));
        $this->assertSame('2,5', IndonesianFormatter::quantity(2.5));
    }

    public function test_formats_timestamp_in_asia_jakarta_wib(): void
    {
        $utcDate = CarbonImmutable::parse('2026-11-17 16:30:00', 'UTC');

        $this->assertSame('17 Nov 2026 23:30 WIB', IndonesianFormatter::wib($utcDate));
        $this->assertSame('-', IndonesianFormatter::wib(null));
    }
}
