<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Redirect;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Slug menu/kategori: otomatis, bisa diubah, alamat lama dialihkan 301 (BR-49, BR-50). */
class SlugRedirectTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(Admin::factory()->create(), 'admin');
        StoreSetting::query()->sole()->forceFill(['name' => 'Toko', 'is_published' => true])->save();
    }

    private function category(string $slug = 'camilan'): Category
    {
        return Category::query()->create(['name' => ucfirst($slug), 'slug' => $slug]);
    }

    private function menu(Category $category, string $slug = 'basreng-pedas'): MenuItem
    {
        return $category->menuItems()->create([
            'name' => 'Basreng Pedas', 'slug' => $slug, 'price' => 15000, 'weight_grams' => 100,
        ]);
    }

    private function renameMenu(MenuItem $menu, string $slug): void
    {
        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->set('item.slug', $slug)
            ->call('saveItem')
            ->assertHasNoErrors();
    }

    public function test_blank_slug_is_generated_from_the_name_and_conflicts_get_a_suffix(): void
    {
        $category = $this->category();

        foreach (['Basreng Pedas', 'Basreng Pedas'] as $name) {
            Livewire::test(Catalog::class)
                ->set('item.category_id', $category->id)
                ->set('item.name', $name)
                ->set('item.price', 12000)
                ->set('item.weight_grams', 100)
                ->call('saveItem')
                ->assertHasNoErrors();
        }

        $this->assertSame(['basreng-pedas', 'basreng-pedas-2'], MenuItem::query()->orderBy('id')->pluck('slug')->all());
    }

    public function test_changing_a_menu_slug_keeps_the_old_address_as_a_301_redirect(): void
    {
        $menu = $this->menu($this->category());

        $this->renameMenu($menu, 'Basreng Super Pedas');

        $this->assertSame('basreng-super-pedas', $menu->refresh()->slug);
        $this->assertDatabaseHas('redirects', ['old_path' => '/menu/basreng-pedas', 'new_path' => '/menu/basreng-super-pedas', 'status_code' => 301]);
        $this->get('/menu/basreng-pedas')->assertStatus(301)->assertRedirect('/menu/basreng-super-pedas');
        $this->get('/menu/basreng-super-pedas')->assertOk();
    }

    public function test_editing_without_changing_the_slug_creates_no_redirect(): void
    {
        $menu = $this->menu($this->category());

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->set('item.price', 16000)
            ->call('saveItem')
            ->assertHasNoErrors();

        $this->assertSame('basreng-pedas', $menu->refresh()->slug);
        $this->assertDatabaseCount('redirects', 0);
    }

    public function test_redirect_chains_are_collapsed_to_a_single_hop(): void
    {
        $menu = $this->menu($this->category(), 'a');

        $this->renameMenu($menu, 'b');
        $this->renameMenu($menu, 'c');

        $this->assertDatabaseCount('redirects', 2);
        $this->assertDatabaseHas('redirects', ['old_path' => '/menu/a', 'new_path' => '/menu/c']);
        $this->assertDatabaseHas('redirects', ['old_path' => '/menu/b', 'new_path' => '/menu/c']);
        $this->get('/menu/a')->assertStatus(301)->assertRedirect('/menu/c');
    }

    public function test_going_back_to_an_earlier_slug_leaves_no_redirect_loop(): void
    {
        $menu = $this->menu($this->category(), 'a');

        $this->renameMenu($menu, 'b');
        $this->renameMenu($menu, 'a');

        $this->assertSame(['/menu/b' => '/menu/a'], Redirect::query()->pluck('new_path', 'old_path')->all());
        $this->get('/menu/a')->assertOk();
        $this->get('/menu/b')->assertStatus(301)->assertRedirect('/menu/a');
    }

    public function test_a_slug_taken_by_another_menu_gets_a_suffix(): void
    {
        $category = $this->category();
        $this->menu($category, 'basreng');
        $other = $this->menu($category, 'keripik');

        $this->renameMenu($other, 'basreng');

        $this->assertSame('basreng-2', $other->refresh()->slug);
    }

    public function test_changing_a_category_slug_redirects_the_old_category_page(): void
    {
        $category = $this->category('camilan');
        $this->menu($category);

        Livewire::test(Catalog::class)
            ->call('editCategory', $category->id)
            ->set('category.slug', 'camilan-pedas')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('redirects', ['old_path' => '/menu/kategori/camilan', 'new_path' => '/menu/kategori/camilan-pedas', 'status_code' => 301]);
        $this->get('/menu/kategori/camilan')->assertStatus(301)->assertRedirect('/menu/kategori/camilan-pedas');
        $this->get('/menu/kategori/camilan-pedas')->assertOk();
    }

    public function test_unknown_address_without_redirect_stays_404(): void
    {
        $this->get('/menu/tidak-ada')->assertNotFound();
    }
}
