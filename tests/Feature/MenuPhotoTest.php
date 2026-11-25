<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Foto menu di admin: unggah → WebP, teks alt bawaan = nama menu (FR-116). */
class MenuPhotoTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        Storage::fake('public');
        $this->actingAs(Admin::factory()->create(), 'admin');
        $this->category = Category::query()->create(['name' => 'Camilan', 'slug' => 'camilan']);
    }

    private function createMenu(?UploadedFile $photo = null, string $alt = ''): MenuItem
    {
        Livewire::test(Catalog::class)
            ->set('item.category_id', $this->category->id)
            ->set('item.name', 'Basreng Pedas')
            ->set('item.price', 12000)
            ->set('item.weight_grams', 100)
            ->set('item.photo_alt', $alt)
            ->set('photo', $photo)
            ->call('saveItem')
            ->assertHasNoErrors();

        return MenuItem::query()->sole();
    }

    public function test_photo_is_saved_as_webp_and_alt_defaults_to_the_menu_name(): void
    {
        $menu = $this->createMenu(UploadedFile::fake()->image('foto.jpg', 1200, 800));

        $this->assertMatchesRegularExpression('#^menu/basreng-pedas-[a-z0-9]{6}\.webp$#', $menu->photo_path);
        Storage::disk('public')->assertExists($menu->photo_path);
        $this->assertNull($menu->photo_alt);
        $this->assertSame('Basreng Pedas', $menu->photo_alt_text);
        $this->assertStringEndsWith('/storage/'.$menu->photo_path, $menu->photo_url);
    }

    public function test_custom_alt_text_is_kept(): void
    {
        $menu = $this->createMenu(UploadedFile::fake()->image('foto.png', 400, 400), '  Basreng pedas dalam kemasan  ');

        $this->assertSame('Basreng pedas dalam kemasan', $menu->photo_alt);
        $this->assertSame('Basreng pedas dalam kemasan', $menu->photo_alt_text);
    }

    public function test_menu_without_photo_has_no_photo_url(): void
    {
        $menu = $this->createMenu();

        $this->assertNull($menu->photo_path);
        $this->assertNull($menu->photo_url);
    }

    public function test_replacing_the_photo_deletes_the_old_file(): void
    {
        $menu = $this->createMenu(UploadedFile::fake()->image('a.jpg', 300, 300));
        $old = $menu->photo_path;

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->set('photo', UploadedFile::fake()->image('b.jpg', 300, 300))
            ->call('saveItem')
            ->assertHasNoErrors();

        $menu->refresh();
        $this->assertNotSame($old, $menu->photo_path);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($menu->photo_path);
    }

    public function test_editing_without_a_new_photo_keeps_the_current_one(): void
    {
        $menu = $this->createMenu(UploadedFile::fake()->image('a.jpg', 300, 300));
        $path = $menu->photo_path;

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->set('item.price', 13000)
            ->call('saveItem')
            ->assertHasNoErrors();

        $this->assertSame($path, $menu->refresh()->photo_path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_photo_can_be_removed(): void
    {
        $menu = $this->createMenu(UploadedFile::fake()->image('a.jpg', 300, 300));
        $path = $menu->photo_path;

        Livewire::test(Catalog::class)->call('editItem', $menu->id)->call('removePhoto')->assertHasNoErrors();

        $this->assertNull($menu->refresh()->photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_invalid_photos_and_long_alt_text_are_rejected(): void
    {
        $form = fn () => Livewire::test(Catalog::class)
            ->set('item.category_id', $this->category->id)
            ->set('item.name', 'Basreng Pedas')
            ->set('item.price', 12000)
            ->set('item.weight_grams', 100);

        $form()->set('photo', UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'))->call('saveItem')->assertHasErrors('photo');
        $form()->set('photo', UploadedFile::fake()->image('besar.jpg')->size(5121))->call('saveItem')->assertHasErrors('photo');
        $form()->set('item.photo_alt', str_repeat('a', 126))->call('saveItem')->assertHasErrors('item.photo_alt');

        $this->assertDatabaseCount('menu_items', 0);
    }
}
