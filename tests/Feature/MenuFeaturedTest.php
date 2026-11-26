<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Menu unggulan: maksimal 8, bisa ditandai langsung dari daftar menu (FR-15, FR-44). */
class MenuFeaturedTest extends TestCase
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

    private function menus(int $count, bool $featured = false): array
    {
        return collect(range(1, $count))->map(fn (int $n) => $this->category->menuItems()->create([
            'name' => 'Menu '.$n.($featured ? ' F' : ''), 'slug' => 'menu-'.$n.($featured ? '-f' : ''),
            'price' => 10000, 'weight_grams' => 100, 'is_featured' => $featured,
        ]))->all();
    }

    public function test_menu_can_be_featured_and_unfeatured_from_the_list(): void
    {
        [$menu] = $this->menus(1);

        Livewire::test(Catalog::class)->call('toggleFeatured', $menu->id)->assertHasNoErrors();
        $this->assertTrue($menu->refresh()->is_featured);

        Livewire::test(Catalog::class)->call('toggleFeatured', $menu->id)->assertHasNoErrors();
        $this->assertFalse($menu->refresh()->is_featured);
    }

    public function test_ninth_featured_menu_is_rejected_but_unfeaturing_still_works(): void
    {
        $this->menus(8, featured: true);
        $ninth = $this->category->menuItems()->create(['name' => 'Menu 9', 'slug' => 'menu-9', 'price' => 10000, 'weight_grams' => 100]);

        Livewire::test(Catalog::class)->call('toggleFeatured', $ninth->id)->assertHasErrors('featured');
        $this->assertFalse($ninth->refresh()->is_featured);

        $first = MenuItem::query()->where('is_featured', true)->first();
        Livewire::test(Catalog::class)->call('toggleFeatured', $first->id)->assertHasNoErrors();
        Livewire::test(Catalog::class)->call('toggleFeatured', $ninth->id)->assertHasNoErrors();

        $this->assertTrue($ninth->refresh()->is_featured);
        $this->assertSame(8, MenuItem::query()->where('is_featured', true)->count());
    }

    public function test_archived_menu_does_not_use_up_a_featured_slot(): void
    {
        $featured = $this->menus(8, featured: true);
        $featured[0]->delete();
        $other = $this->category->menuItems()->create(['name' => 'Menu 9', 'slug' => 'menu-9', 'price' => 10000, 'weight_grams' => 100]);

        Livewire::test(Catalog::class)->call('toggleFeatured', $other->id)->assertHasNoErrors();

        $this->assertTrue($other->refresh()->is_featured);
    }

    public function test_the_form_checkbox_still_enforces_the_limit(): void
    {
        $this->menus(8, featured: true);

        Livewire::test(Catalog::class)
            ->set('item.category_id', $this->category->id)
            ->set('item.name', 'Menu Baru')
            ->set('item.price', 10000)
            ->set('item.weight_grams', 100)
            ->set('item.is_featured', true)
            ->call('saveItem')
            ->assertHasErrors('item.is_featured');
    }

    public function test_list_shows_the_featured_counter(): void
    {
        $this->menus(3, featured: true);

        Livewire::test(Catalog::class)->assertSee('Menu unggulan: 3 dari 8');
    }

    public function test_toggling_from_the_list_updates_the_open_edit_form(): void
    {
        [$menu] = $this->menus(1);

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->assertSet('item.is_featured', false)
            ->call('toggleFeatured', $menu->id)
            ->assertSet('item.is_featured', true);
    }
}
