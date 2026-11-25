<?php

namespace App\Jobs;

use GdImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/** Ubah gambar unggahan jadi WebP (maks. 1600 px lebar), simpan di disk public, isi kolom model. */
class ConvertImageToWebp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const MAX_WIDTH = 1600;

    public const QUALITY = 82;

    public int $tries = 3;

    /** @param  class-string<Model>  $modelClass */
    public function __construct(
        public string $modelClass,
        public int|string $modelId,
        public string $column,
        public string $tempPath,
        public string $baseName,
        public string $directory,
    ) {}

    public function handle(): void
    {
        $local = Storage::disk('local');
        $public = Storage::disk('public');

        $model = $this->findModel();

        if ($model === null || ! $local->exists($this->tempPath)) {
            $local->delete($this->tempPath);

            return;
        }

        $name = substr(Str::slug($this->baseName) ?: 'foto', 0, 50);
        $path = trim($this->directory, '/').'/'.$name.'-'.Str::lower(Str::random(6)).'.webp';

        $public->put($path, $this->toWebp($local->path($this->tempPath)));

        $old = $model->getAttribute($this->column);
        $model->forceFill([$this->column => $path])->save();

        if (is_string($old) && $old !== '' && $old !== $path) {
            $public->delete($old);
        }

        $local->delete($this->tempPath);
    }

    public function failed(?Throwable $exception): void
    {
        Storage::disk('local')->delete($this->tempPath);
    }

    private function findModel(): ?Model
    {
        $query = $this->modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($this->modelClass), true)) {
            $query->withTrashed();
        }

        return $query->find($this->modelId);
    }

    private function toWebp(string $file): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file));

        if ($image === false) {
            throw new RuntimeException('Gambar tidak bisa dibaca.');
        }

        $image = $this->orient($this->toTrueColor($image), $file);

        if (imagesx($image) > self::MAX_WIDTH) {
            $image = imagescale($image, self::MAX_WIDTH) ?: $image;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $data = (string) ob_get_clean();

        if (! $ok || $data === '') {
            throw new RuntimeException('Konversi ke WebP gagal.');
        }

        return $data;
    }

    /** PNG berpalet (256 warna) tidak bisa langsung jadi WebP; salin ke kanvas true color. */
    private function toTrueColor(GdImage $image): GdImage
    {
        if (imageistruecolor($image)) {
            return $image;
        }

        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, (int) imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        return $canvas;
    }

    /** Foto dari HP sering menyimpan rotasi di EXIF; tanpa ini hasilnya bisa miring. */
    private function orient(GdImage $image, string $file): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file);
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angle === 0 ? $image : (imagerotate($image, $angle, 0) ?: $image);
    }
}
