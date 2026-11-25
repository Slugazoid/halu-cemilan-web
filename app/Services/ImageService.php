<?php

namespace App\Services;

use App\Jobs\ConvertImageToWebp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

/**
 * Unggah foto: validasi (JPG/PNG/WebP, maks. 5 MB), lalu konversi ke WebP
 * lewat queue. Dipakai Modul A (foto menu) dan Modul B (logo, galeri, sampul).
 */
final class ImageService
{
    public const MAX_KB = 5120;

    /**
     * Aturan validasi untuk dipasang di form Livewire (`#[Validate]` / `$this->validate()`).
     *
     * @return list<string>
     */
    public static function rules(): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_KB];
    }

    /**
     * Simpan upload sementara, lalu antrekan konversi WebP. Kolom `$column` pada
     * `$model` terisi setelah job selesai (butuh `php artisan queue:work`).
     *
     * @param  string  $baseName  dasar nama file, mis. slug menu → menu/basreng-pedas-x7k2qa.webp
     * @param  string  $directory  folder di disk public, mis. 'menu'
     */
    public function queue(Model $model, string $column, UploadedFile $file, string $baseName, string $directory): void
    {
        Validator::make(['foto' => $file], ['foto' => self::rules()])->validate();

        $temp = $file->store('tmp/images', 'local');

        ConvertImageToWebp::dispatch($model::class, $model->getKey(), $column, $temp, $baseName, $directory);
    }
}
