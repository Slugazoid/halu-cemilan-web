<?php

namespace App\Concerns;

use App\Support\PublicCache;

/** Pasang di model yang tampil di halaman publik: simpan/hapus → cache publik dibersihkan. */
trait ClearsPublicCache
{
    public static function bootClearsPublicCache(): void
    {
        $flush = static fn () => PublicCache::flush();

        static::saved($flush);
        static::deleted($flush);

        if (method_exists(static::class, 'restored')) {
            static::restored($flush);
        }
    }
}
