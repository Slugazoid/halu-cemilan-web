<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache konten publik dengan "versi". Driver cache = database (tanpa tag),
 * jadi membersihkan cache = menaikkan versi; kunci lama otomatis tidak terpakai
 * dan kedaluwarsa sendiri. Dipanggil lewat model event saat admin menyimpan
 * perubahan (lihat trait ClearsPublicCache).
 */
final class PublicCache
{
    private const VERSION_KEY = 'public-cache:version';

    private const DEFAULT_TTL = 3600;

    public static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn () => 1);
    }

    public static function key(string $name): string
    {
        return 'public:v'.self::version().':'.$name;
    }

    /**
     * @template T
     *
     * @param  Closure():T  $callback
     * @return T
     */
    public static function remember(string $name, Closure $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(self::key($name), $ttl ?? self::DEFAULT_TTL, $callback);
    }

    /** Buang semua cache publik. */
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
