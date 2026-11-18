<?php

namespace App\Support;

/** Utilitas warna untuk tema dinamis (kontras WCAG AA). */
final class Color
{
    public static function isHex(?string $hex): bool
    {
        return is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1;
    }

    /** Luminansi relatif 0–1 menurut WCAG. */
    public static function luminance(string $hex): float
    {
        [$r, $g, $b] = array_map(
            fn (string $c) => hexdec($c) / 255,
            str_split(ltrim($hex, '#'), 2),
        );

        $lin = fn (float $c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);
    }

    public static function contrastRatio(string $a, string $b): float
    {
        $la = self::luminance($a);
        $lb = self::luminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /** Teks putih atau gelap, mana yang kontrasnya lebih tinggi di atas $background. */
    public static function textOn(string $background): string
    {
        return self::contrastRatio($background, '#FFFFFF') >= self::contrastRatio($background, '#1F1F1F')
            ? '#FFFFFF'
            : '#1F1F1F';
    }
}
