<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Support\SlugGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_clean_lowercase_slug(): void
    {
        $this->assertSame('usus-ayam-crispy-pedas', SlugGenerator::unique(MenuItem::class, 'Usus Ayam Crispy  Pedas!'));
    }

    public function test_conflicting_slug_gets_numeric_suffix(): void
    {
        $this->seed();

        $this->assertSame('basreng-original-2', SlugGenerator::unique(MenuItem::class, 'Basreng Original'));
    }

    public function test_slug_is_kept_when_editing_the_same_record(): void
    {
        $this->seed();
        $menu = MenuItem::where('slug', 'basreng-original')->firstOrFail();

        $this->assertSame('basreng-original', SlugGenerator::unique(MenuItem::class, 'Basreng Original', $menu->id));
    }

    public function test_archived_menu_still_reserves_its_slug(): void
    {
        $this->seed();
        MenuItem::where('slug', 'basreng-original')->firstOrFail()->delete();

        $this->assertSame('basreng-original-2', SlugGenerator::unique(MenuItem::class, 'Basreng Original'));
    }

    public function test_slug_is_at_most_60_characters_even_with_suffix(): void
    {
        $name = str_repeat('a', 80);
        $first = SlugGenerator::unique(Category::class, $name);
        Category::create(['name' => $name, 'slug' => $first]);
        $second = SlugGenerator::unique(Category::class, $name);

        $this->assertSame(60, strlen($first));
        $this->assertLessThanOrEqual(60, strlen($second));
        $this->assertStringEndsWith('-2', $second);
    }

    public function test_symbol_only_name_falls_back_to_item(): void
    {
        $this->assertSame('item', SlugGenerator::unique(Category::class, '!!!'));
    }
}
