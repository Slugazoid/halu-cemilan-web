<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\MenuOptionGroup;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Varian/opsi per menu: grup wajib/opsional, tambahan harga & berat (FR-13, BR-04, BR-06, BR-37, BR-55). */
class MenuOptionTest extends TestCase
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

    private function menuWithOptions(): MenuItem
    {
        $menu = $this->category->menuItems()->create([
            'name' => 'Basreng Pedas', 'slug' => 'basreng-pedas', 'price' => 12000, 'weight_grams' => 100,
        ]);
        $level = $menu->optionGroups()->create(['name' => 'Level pedas', 'is_required' => true, 'sort_order' => 0]);
        $level->options()->createMany([
            ['name' => 'Original', 'sort_order' => 0],
            ['name' => 'Level 3', 'extra_price' => 500, 'sort_order' => 1],
        ]);

        return $menu;
    }

    public function test_group_and_options_are_saved_with_price_weight_and_order(): void
    {
        $this->form()
            ->call('addGroup')
            ->set('groups.0.name', 'Ukuran')
            ->set('groups.0.is_required', true)
            ->set('groups.0.options.0.name', '100 g')
            ->call('addOption', 0)
            ->set('groups.0.options.1.name', '250 g')
            ->set('groups.0.options.1.extra_price', 25000)
            ->set('groups.0.options.1.extra_weight_grams', 150)
            ->call('saveItem')
            ->assertHasNoErrors();

        $group = MenuItem::query()->sole()->optionGroups()->sole();
        $this->assertSame('Ukuran', $group->name);
        $this->assertTrue($group->is_required);

        $options = $group->options;
        $this->assertSame(['100 g', '250 g'], $options->pluck('name')->all());
        $this->assertSame([0, 25000], $options->pluck('extra_price')->all());
        $this->assertSame([0, 150], $options->pluck('extra_weight_grams')->all());
        $this->assertSame([0, 1], $options->pluck('sort_order')->all());
    }

    public function test_menu_without_variants_still_saves(): void
    {
        $this->form()->call('saveItem')->assertHasNoErrors();

        $this->assertDatabaseCount('menu_option_groups', 0);
    }

    public function test_editing_loads_the_existing_groups(): void
    {
        $menu = $this->menuWithOptions();

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->assertSet('groups.0.name', 'Level pedas')
            ->assertSet('groups.0.is_required', true)
            ->assertSet('groups.0.options.1.name', 'Level 3')
            ->assertSet('groups.0.options.1.extra_price', 500);
    }

    public function test_existing_ids_are_kept_new_ones_added_and_removed_ones_deleted(): void
    {
        $menu = $this->menuWithOptions();
        $group = $menu->optionGroups()->sole();
        [$original, $level3] = $group->options->all();

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->set('groups.0.options.1.name', 'Level 3 (pedas banget)')
            ->call('removeOption', 0, 0)
            ->call('addOption', 0)
            ->set('groups.0.options.1.name', 'Level 5')
            ->call('saveItem')
            ->assertHasNoErrors();

        $options = $group->refresh()->options;
        $this->assertSame($group->id, $menu->optionGroups()->sole()->id);
        $this->assertSame(['Level 3 (pedas banget)', 'Level 5'], $options->pluck('name')->all());
        $this->assertSame($level3->id, $options->first()->id);
        $this->assertDatabaseMissing('menu_options', ['id' => $original->id]);
    }

    public function test_removed_group_is_deleted_with_its_options(): void
    {
        $menu = $this->menuWithOptions();

        Livewire::test(Catalog::class)->call('editItem', $menu->id)->call('removeGroup', 0)->call('saveItem')->assertHasNoErrors();

        $this->assertDatabaseCount('menu_option_groups', 0);
        $this->assertDatabaseCount('menu_options', 0);
    }

    public function test_group_without_options_and_invalid_numbers_are_rejected(): void
    {
        $this->form()->call('addGroup')->set('groups.0.name', 'Ukuran')->call('removeOption', 0, 0)->call('saveItem')->assertHasErrors('groups.0.options');

        $this->form()->call('addGroup')->set('groups.0.name', '')->set('groups.0.options.0.name', 'Opsi')->call('saveItem')->assertHasErrors('groups.0.name');

        $this->form()->call('addGroup')->set('groups.0.name', 'Ukuran')->set('groups.0.options.0.name', '250 g')
            ->set('groups.0.options.0.extra_price', -1)->call('saveItem')->assertHasErrors('groups.0.options.0.extra_price');

        $this->form()->call('addGroup')->set('groups.0.name', 'Ukuran')->set('groups.0.options.0.name', '250 g')
            ->set('groups.0.options.0.extra_weight_grams', 65536)->call('saveItem')->assertHasErrors('groups.0.options.0.extra_weight_grams');

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_a_group_id_from_another_menu_is_never_touched(): void
    {
        $other = $this->menuWithOptions();
        $foreignGroup = $other->optionGroups()->sole();
        $mine = $this->category->menuItems()->create(['name' => 'Seblak', 'slug' => 'seblak', 'price' => 15000, 'weight_grams' => 200]);

        Livewire::test(Catalog::class)
            ->call('editItem', $mine->id)
            ->set('groups', [['id' => $foreignGroup->id, 'name' => 'Dibajak', 'is_required' => false, 'options' => [['id' => null, 'name' => 'X', 'extra_price' => 0, 'extra_weight_grams' => 0]]]])
            ->call('saveItem')
            ->assertHasNoErrors();

        $this->assertSame('Level pedas', $foreignGroup->refresh()->name);
        $this->assertSame($other->id, $foreignGroup->menu_item_id);
        $this->assertSame('Dibajak', $mine->optionGroups()->sole()->name);
    }

    public function test_seeded_variants_load_and_survive_an_unchanged_save(): void
    {
        $this->seed();
        $menu = MenuItem::query()->where('name', 'Usus Ayam Crispy')->firstOrFail();
        $before = MenuOption::query()->whereIn('menu_option_group_id', $menu->optionGroups()->pluck('id'))->pluck('id')->all();

        $this->assertNotEmpty($before);

        Livewire::test(Catalog::class)->call('editItem', $menu->id)->call('saveItem')->assertHasNoErrors();

        $after = MenuOption::query()->whereIn('menu_option_group_id', $menu->optionGroups()->pluck('id'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing($before, $after);
        $this->assertSame(2, MenuOptionGroup::query()->where('menu_item_id', $menu->id)->count());
    }

    public function test_cancelling_the_form_clears_the_groups(): void
    {
        $menu = $this->menuWithOptions();

        Livewire::test(Catalog::class)->call('editItem', $menu->id)->assertCount('groups', 1)->call('cancelItem')->assertSet('groups', []);
    }
}
