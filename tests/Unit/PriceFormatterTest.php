<?php

namespace Tests\Unit;

use App\Support\PriceFormatter;
use PHPUnit\Framework\TestCase;

class PriceFormatterTest extends TestCase
{
    public function test_format_vi(): void
    {
        $this->assertSame('250.000₫', PriceFormatter::format(250000, 'vi'));
    }

    public function test_format_en(): void
    {
        $this->assertSame('$250,000.00', PriceFormatter::format(250000, 'en'));
    }

    public function test_format_zero(): void
    {
        $this->assertSame('0₫', PriceFormatter::format(0, 'vi'));
        $this->assertSame('$0.00', PriceFormatter::format(0, 'en'));
    }

    public function test_format_decimal(): void
    {
        $this->assertSame('150.500₫', PriceFormatter::format(150500, 'vi'));
        $this->assertSame('$150,500.00', PriceFormatter::format(150500, 'en'));
    }
}
