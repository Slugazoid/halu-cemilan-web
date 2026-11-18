<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Slug huruf kecil/angka/tanda hubung, maks. 60 karakter, unik per jenis.
 * Slug bentrok diberi akhiran -2, -3, … (BR-49). Soft delete ikut dihitung
 * karena kolom slug bersifat UNIQUE di database.
 */
final class SlugGenerator
{
    public const MAX_LENGTH = 60;

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function unique(string $modelClass, string $name, ?int $ignoreId = null): string
    {
        $base = Str::of(Str::slug($name))->limit(self::MAX_LENGTH, '')->trim('-')->toString();
        $base = $base !== '' ? $base : 'item';

        $candidate = $base;
        $n = 2;

        while (self::exists($modelClass, $candidate, $ignoreId)) {
            $suffix = '-'.$n++;
            $candidate = Str::of($base)->limit(self::MAX_LENGTH - strlen($suffix), '')->trim('-').$suffix;
        }

        return $candidate;
    }

    private static function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        return $query
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }
}
