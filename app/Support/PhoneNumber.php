<?php

namespace App\Support;

/**
 * Normalisasi nomor HP Indonesia ke format 628… (BR-12).
 * Dipakai untuk admin, nomor WA toko, dan pembatasan kode promo per pelanggan.
 */
final class PhoneNumber
{
    /** Hasil normalisasi, atau null bila tidak valid (10–15 digit, diawali 628). */
    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $input);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        if (! str_starts_with($digits, '628')) {
            return null;
        }

        $length = strlen($digits);

        return ($length >= 10 && $length <= 15) ? $digits : null;
    }

    public static function isValid(?string $input): bool
    {
        return self::normalize($input) !== null;
    }

    /** Tampilan ramah baca: 0812-3456-7890. */
    public static function display(string $normalized): string
    {
        $local = '0'.substr($normalized, 2);

        return trim(chunk_split($local, 4, '-'), '-');
    }
}
