<?php

namespace Database\Seeders;

use RuntimeException;

/** Membaca mock/halu-cemilan.json (konten placeholder sampai data asli mitra ada). */
final class MockData
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = base_path('mock/halu-cemilan.json');

        if (! is_file($path)) {
            throw new RuntimeException("File mock tidak ditemukan: {$path}");
        }

        return self::$cache = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function get(string $key): mixed
    {
        return self::all()[$key] ?? [];
    }
}
