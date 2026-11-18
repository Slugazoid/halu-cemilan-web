<?php

namespace App\Models;

use App\Concerns\ClearsPublicCache;
use App\Support\Color;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Model;

/** Pengaturan toko. Selalu tepat satu baris; ambil lewat StoreSetting::current(). */
class StoreSetting extends Model
{
    use ClearsPublicCache;

    /** Baris tunggal pengaturan toko (dibuat oleh migration). */
    public const int SINGLETON_ID = 1;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'custom_labels' => 'array',
            'opening_hours' => 'array',
            'social_links' => 'array',
            'meta_templates' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'order_sequence' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /** Baris pengaturan toko (di-cache; dibersihkan otomatis saat disimpan). Null bila belum di-seed. */
    public static function current(): ?self
    {
        return PublicCache::remember('store-settings', fn () => static::query()->first());
    }

    /** Daftar font yang boleh dipilih admin → nama keluarga CSS + fallback. */
    public static function fontStack(string $font): string
    {
        $fallback = config('theme.fallback_stack');

        return "'{$font}', {$fallback}";
    }

    /** CSS variables tema yang disuntikkan ke <head> layout. */
    public function themeCss(): string
    {
        $primary = Color::isHex($this->primary_color) ? $this->primary_color : config('theme.default_primary');
        $accent = Color::isHex($this->accent_color) ? $this->accent_color : config('theme.default_accent');
        $font = array_key_exists($this->font, config('theme.fonts')) ? $this->font : config('theme.default_font');

        return implode('', [
            ':root{',
            "--store-primary: {$primary};",
            '--store-on-primary: '.Color::textOn($primary).';',
            "--store-accent: {$accent};",
            '--store-on-accent: '.Color::textOn($accent).';',
            '--store-font: '.static::fontStack($font).';',
            '}',
        ]);
    }

    /** URL Google Fonts untuk font terpilih (null bila font sistem). */
    public function fontUrl(): ?string
    {
        $font = array_key_exists($this->font, config('theme.fonts')) ? $this->font : config('theme.default_font');

        return config("theme.fonts.{$font}");
    }
}
