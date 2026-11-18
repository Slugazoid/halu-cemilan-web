<?php

namespace Tests\Unit;

use App\Support\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function test_hex_validation(): void
    {
        $this->assertTrue(Color::isHex('#D9381E'));
        $this->assertFalse(Color::isHex('D9381E'));
        $this->assertFalse(Color::isHex('#FFF'));
        $this->assertFalse(Color::isHex(null));
    }

    public function test_text_color_has_aa_contrast_on_any_background(): void
    {
        foreach (['#D9381E', '#F2A900', '#FFFFFF', '#000000', '#2E7D32', '#FFEB3B', '#1A237E'] as $bg) {
            $text = Color::textOn($bg);
            $this->assertGreaterThanOrEqual(4.5, Color::contrastRatio($bg, $text), "Kontras {$bg} vs {$text}");
        }
    }

    public function test_picks_white_on_dark_and_dark_on_light(): void
    {
        $this->assertSame('#FFFFFF', Color::textOn('#1A237E'));
        $this->assertSame('#1F1F1F', Color::textOn('#FFEB3B'));
    }
}
