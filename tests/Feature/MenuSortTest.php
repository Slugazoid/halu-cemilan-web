<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Urutan kategori dan menu lewat seret-dan-lepas (FR-44). */
class MenuSortTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    private function categories(int $count): array
    {
        return collect(range(1, $count))->map(fn (int $n) => Category::query()->create([
            'name' => 'Kategori '.$n, 'slug' => 'kategori-'.$n, 'sort_order' => $n,
        ]))->all();
    }

    private function menus(int $count): array
    {
        $category = Category::query()->create(['name' => 'Camilan', 'slug' => 'camilan']);

        return collect(range(1, $count))->map(fn (int $n) => $category->menuItems()->create([
            'name' => 'Menu '.$n, 'slug' => 'menu-'.$n, 'price' => 10000, 'weight_grams' => 100, 'sort_order' => $n,
        ]))->all();
    }

    private function categoryOrder(): array
    {
        return Category::query()->orderBy('sort_order')->orderBy('name')->pluck('name')->all();
    }

    private function menuOrder(): array
    {
        return MenuItem::query()->orderBy('sort_order')->orderBy('id')->pluck('name')->all();
    }

    public function test_category_can_be_dragged_to_a_new_position(): void
    {
        [$one, , $three] = $this->categories(3);

        Livewire::test(Catalog::class)->call('sortCategory', $three->id, 0)->assertHasNoErrors();

        $this->assertSame(['Kategori 3', 'Kategori 1', 'Kategori 2'], $this->categoryOrder());

        Livewire::test(Catalog::class)->call('sortCategory', (string) $three->id, 2);

        $this->assertSame(['Kategori 1', 'Kategori 2', 'Kategori 3'], $this->categoryOrder());
        $this->assertSame([1, 2, 3], Category::query()->orderBy('sort_order')->pluck('sort_order')->all());
        $this->assertSame(1, $one->refresh()->sort_order);
    }

    public function test_menu_can_be_dragged_to_a_new_position(): void
    {
        [$one, , , $four] = $this->menus(4);

        Livewire::test(Catalog::class)->call('sortItem', $four->id, 1)->assertHasNoErrors();

        $this->assertSame(['Menu 1', 'Menu 4', 'Menu 2', 'Menu 3'], $this->menuOrder());
        $this->assertSame([1, 2, 3, 4], MenuItem::query()->orderBy('sort_order')->pluck('sort_order')->all());
        $this->assertSame(1, $one->refresh()->sort_order);
    }

    public function test_equal_sort_orders_are_normalised(): void
    {
        $category = Category::query()->create(['name' => 'Camilan', 'slug' => 'camilan']);
        $first = $category->menuItems()->create(['name' => 'A', 'slug' => 'a', 'price' => 1000, 'weight_grams' => 100]);
        $category->menuItems()->create(['name' => 'B', 'slug' => 'b', 'price' => 1000, 'weight_grams' => 100]);
        $category->menuItems()->create(['name' => 'C', 'slug' => 'c', 'price' => 1000, 'weight_grams' => 100]);

        Livewire::test(Catalog::class)->call('sortItem', $first->id, 2);

        $this->assertSame(['B', 'C', 'A'], $this->menuOrder());
        $this->assertSame([1, 2, 3], MenuItem::query()->orderBy('sort_order')->pluck('sort_order')->all());
    }

    public function test_dragging_on_page_two_keeps_the_global_order(): void
    {
        $menus = $this->menus(14);

        Livewire::test(Catalog::class)
            ->set('paginators.page', 2)
            ->call('sortItem', $menus[13]->id, 0)
            ->assertHasNoErrors();

        $order = $this->menuOrder();
        $this->assertSame('Menu 14', $order[12]);
        $this->assertSame('Menu 13', $order[13]);
        $this->assertSame('Menu 1', $order[0]);
        $this->assertSame('Menu 12', $order[11]);
    }

    public function test_unknown_or_archived_ids_change_nothing(): void
    {
        [$one] = $this->menus(3);
        $before = $this->menuOrder();

        Livewire::test(Catalog::class)->call('sortItem', 99999, 0)->assertHasNoErrors();
        $one->delete();
        Livewire::test(Catalog::class)->call('sortItem', $one->id, 1)->assertHasNoErrors();

        $this->assertSame(['Menu 2', 'Menu 3'], $this->menuOrder());
        $this->assertSame(['Menu 1', 'Menu 2', 'Menu 3'], $before);
    }

    public function test_position_beyond_the_end_is_clamped(): void
    {
        [$one] = $this->menus(3);

        Livewire::test(Catalog::class)->call('sortItem', $one->id, 50);

        $this->assertSame(['Menu 2', 'Menu 3', 'Menu 1'], $this->menuOrder());
    }

    public function test_open_edit_form_shows_the_new_order_number(): void
    {
        [$one, , $three] = $this->menus(3);

        Livewire::test(Catalog::class)
            ->call('editItem', $three->id)
            ->assertSet('item.sort_order', 3)
            ->call('sortItem', $three->id, 0)
            ->assertSet('item.sort_order', 1);

        $this->assertSame(2, $one->refresh()->sort_order);
    }

    public function test_list_renders_drag_handles(): void
    {
        $this->menus(2);

        Livewire::test(Catalog::class)->assertSeeHtml('wire:sort="sortItem"')->assertSeeHtml('wire:sort:handle');
    }
}
