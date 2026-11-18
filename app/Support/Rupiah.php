<?php

namespace App\Support;

final class Rupiah
{
    /** 25000 → "Rp 25.000" */
    public static function format(int|float|null $amount): string
    {
        $amount = (int) round($amount ?? 0);
        $sign = $amount < 0 ? '-' : '';

        return $sign.'Rp '.number_format(abs($amount), 0, ',', '.');
    }

    /** Bulatkan ke bawah ke kelipatan Rp100, minimal Rp100 (BR-36). */
    public static function floorToHundred(int $amount): int
    {
        return max(100, intdiv($amount, 100) * 100);
    }
}
