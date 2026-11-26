<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Label menu: Best Seller, Pedas, Baru (FR-12). */
class MenuLabelTest extends TestCase
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

    public function test_labels_are_saved_in_the_official_order(): void
    {
        $this->form()->set('item.labels', ['Baru', 'Best Seller'])->call('saveItem')->assertHasNoErrors();

        $this->assertSame(['Best Seller', 'Baru'], MenuItem::query()->sole()->labels);
    }

    public function test_menu_without_labels_stores_null(): void
    {
        $this->form()->call('saveItem')->assertHasNoErrors();

        $this->assertNull(MenuItem::query()->sole()->labels);
    }

    public function test_unknown_label_is_rejected(): void
    {
        $this->form()->set('item.labels', ['Gratis Ongkir'])->call('saveItem')->assertHasErrors('item.labels.0');

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_editing_loads_current_labels_and_they_can_be_cleared(): void
    {
        $menu = $this->category->menuItems()->create([
            'name' => 'Basreng Pedas', 'slug' => 'basreng-pedas', 'price' => 12000, 'weight_grams' => 100, 'labels' => ['Pedas'],
        ]);

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->assertSet('item.labels', ['Pedas'])
            ->set('item.labels', [])
            ->call('saveItem')
            ->assertHasNoErrors();

        $this->assertNull($menu->refresh()->labels);
    }

    public function test_labels_show_in_the_admin_menu_list(): void
    {
        $this->category->menuItems()->create([
            'name' => 'Basreng Pedas', 'slug' => 'basreng-pedas', 'price' => 12000, 'weight_grams' => 100, 'labels' => ['Best Seller', 'Pedas'],
        ]);

        Livewire::test(Catalog::class)->assertSee('Best Seller, Pedas');
    }

    public function test_every_label_in_the_seed_data_is_a_valid_label(): void
    {
        $this->seed();

        foreach (MenuItem::query()->whereNotNull('labels')->get() as $menu) {
            $this->assertSame([], array_diff($menu->labels, MenuItem::LABELS), $menu->name);
        }
    }
}
