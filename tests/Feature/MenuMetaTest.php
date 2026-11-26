<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Judul & deskripsi SEO per menu dan kategori; kosong = template bawaan (BR-53). Dikelola Staf maupun Pemilik (BR-34). */
class MenuMetaTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(Admin::factory()->create(), 'admin');
        $this->category = Category::query()->create(['name' => 'Camilan', 'slug' => 'camilan']);
    }

    private function form()
    {
        return Livewire::test(Catalog::class)
            ->set('item.category_id', $this->category->id)
            ->set('item.name', 'Basreng Pedas')
            ->set('item.price', 12000)
            ->set('item.weight_grams', 100);
    }

    public function test_menu_meta_is_saved_trimmed(): void
    {
        $this->form()
            ->set('item.meta_title', '  Basreng Pedas Bandung  ')
            ->set('item.meta_description', 'Basreng pedas renyah khas Bandung.')
            ->call('saveItem')
            ->assertHasNoErrors();

        $menu = MenuItem::query()->sole();
        $this->assertSame('Basreng Pedas Bandung', $menu->meta_title);
        $this->assertSame('Basreng pedas renyah khas Bandung.', $menu->meta_description);
    }

    public function test_blank_menu_meta_falls_back_to_null_and_can_be_cleared(): void
    {
        $this->form()->call('saveItem')->assertHasNoErrors();
        $menu = MenuItem::query()->sole();
        $this->assertNull($menu->meta_title);
        $this->assertNull($menu->meta_description);

        $menu->update(['meta_title' => 'Judul lama', 'meta_description' => 'Deskripsi lama']);

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->assertSet('item.meta_title', 'Judul lama')
            ->set('item.meta_title', '')
            ->set('item.meta_description', '   ')
            ->call('saveItem')
            ->assertHasNoErrors();

        $this->assertNull($menu->refresh()->meta_title);
        $this->assertNull($menu->meta_description);
    }

    public function test_menu_meta_length_limits(): void
    {
        $this->form()->set('item.meta_title', str_repeat('a', 71))->call('saveItem')->assertHasErrors('item.meta_title');
        $this->form()->set('item.meta_description', str_repeat('a', 171))->call('saveItem')->assertHasErrors('item.meta_description');
        $this->form()->set('item.meta_title', str_repeat('a', 70))->set('item.meta_description', str_repeat('a', 170))->call('saveItem')->assertHasNoErrors();
    }

    public function test_category_meta_is_saved_loaded_and_cleared(): void
    {
        Livewire::test(Catalog::class)
            ->call('editCategory', $this->category->id)
            ->set('category.meta_title', 'Camilan Pedas Bandung')
            ->set('category.meta_description', 'Semua camilan pedas.')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertSame('Camilan Pedas Bandung', $this->category->refresh()->meta_title);

        Livewire::test(Catalog::class)
            ->call('editCategory', $this->category->id)
            ->assertSet('category.meta_title', 'Camilan Pedas Bandung')
            ->set('category.meta_title', '')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertNull($this->category->refresh()->meta_title);
    }

    public function test_category_meta_length_limits(): void
    {
        Livewire::test(Catalog::class)->set('category.name', 'Basreng')->set('category.meta_title', str_repeat('a', 71))->call('saveCategory')->assertHasErrors('category.meta_title');
        Livewire::test(Catalog::class)->set('category.name', 'Basreng')->set('category.meta_description', str_repeat('a', 171))->call('saveCategory')->assertHasErrors('category.meta_description');
    }
}
