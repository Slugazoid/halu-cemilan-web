<?php

namespace Tests\Unit;

use App\Support\Rupiah;
use PHPUnit\Framework\TestCase;

class RupiahTest extends TestCase
{
    public function test_formats_with_thousand_separator(): void
    {
        $this->assertSame('Rp 25.000', Rupiah::format(25000));
        $this->assertSame('Rp 0', Rupiah::format(0));
        $this->assertSame('Rp 1.250.000', Rupiah::format(1250000));
        $this->assertSame('Rp 0', Rupiah::format(null));
    }

    public function test_floor_to_hundred_with_minimum_100(): void
    {
        // BR-36
        $this->assertSame(16200, Rupiah::floorToHundred(16299));
        $this->assertSame(16300, Rupiah::floorToHundred(16300));
        $this->assertSame(100, Rupiah::floorToHundred(99));
        $this->assertSame(100, Rupiah::floorToHundred(0));
    }
}
