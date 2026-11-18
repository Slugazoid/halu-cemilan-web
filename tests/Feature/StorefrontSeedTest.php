<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Data seeder tampil lewat storefront dan tema toko disuntikkan ke layout. */
class StorefrontSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_store_and_menu_appear_on_the_storefront(): void
    {
        $this->seed();

        $this->get('/')->assertOk()->assertSee('Halu Cemilan Instan')->assertSee('Camilan pedas gurih khas Bandung');
        $this->get(route('store.catalog'))->assertOk()
            ->assertSee('Usus Ayam Crispy')->assertSee('Rp 18.000')->assertSee('Makaroni Spiral Balado');
    }

    public function test_hidden_menu_is_not_shown_in_catalog(): void
    {
        $this->seed();
        MenuItem::where('slug', 'basreng-original')->update(['is_visible' => false]);

        $this->get(route('store.catalog'))->assertDontSee('Basreng Original')->assertSee('Basreng Pedas Daun Jeruk');
    }

    public function test_theme_css_variables_and_font_come_from_store_settings(): void
    {
        $this->seed();
        StoreSetting::query()->first()->update(['primary_color' => '#1A237E', 'accent_color' => '#FFEB3B']);

        $this->get('/')
            ->assertSee('--store-primary: #1A237E;', false)
            ->assertSee('--store-on-primary: #FFFFFF;', false)
            ->assertSee('--store-accent: #FFEB3B;', false)
            ->assertSee('--store-on-accent: #1F1F1F;', false)
            ->assertSee('fonts.googleapis.com/css2?family=Poppins', false);
    }

    public function test_invalid_color_and_font_fall_back_to_defaults(): void
    {
        $this->seed();
        StoreSetting::query()->first()->update(['primary_color' => 'red', 'font' => 'Comic Sans']);

        $css = StoreSetting::current()->themeCss();

        $this->assertStringContainsString('--store-primary: '.config('theme.default_primary').';', $css);
        $this->assertStringContainsString("'".config('theme.default_font')."'", $css);
    }

    public function test_fresh_install_has_one_empty_store_row_and_shows_coming_soon(): void
    {
        $this->assertSame(1, StoreSetting::count());

        $this->get('/')->assertOk()->assertSee('Segera hadir')->assertSee('noindex', false);
    }
}
