<?php

namespace App\Services;

use App\Models\Redirect;
use Illuminate\Support\Facades\DB;

/**
 * Alihan 301 saat slug menu/kategori berubah (BR-49, BR-50).
 * Rantai selalu diringkas: A→B lalu B→C menjadi A→C dan B→C (satu lompatan).
 */
final class RedirectService
{
    public function record(string $oldPath, string $newPath): void
    {
        $old = $this->normalize($oldPath);
        $new = $this->normalize($newPath);

        if ($old === $new) {
            return;
        }

        DB::transaction(function () use ($old, $new): void {
            // Alamat tujuan sekarang hidup lagi: tidak boleh menjadi sumber alihan (cegah putaran).
            Redirect::query()->where('old_path', $new)->delete();
            // Ringkas rantai: semua yang menuju alamat lama sekarang langsung menuju alamat baru.
            Redirect::query()->where('new_path', $old)->update(['new_path' => $new]);
            Redirect::query()->updateOrCreate(['old_path' => $old], ['new_path' => $new, 'status_code' => 301]);
        });
    }

    /** Hapus alihan yang memakai $path sebagai sumber (alamat itu dipakai halaman baru). */
    public function release(string $path): void
    {
        Redirect::query()->where('old_path', $this->normalize($path))->delete();
    }

    public function resolve(string $path): ?Redirect
    {
        return Redirect::query()->where('old_path', $this->normalize($path))->first();
    }

    private function normalize(string $path): string
    {
        $path = '/'.trim($path, '/');

        return $path === '' ? '/' : $path;
    }
}
