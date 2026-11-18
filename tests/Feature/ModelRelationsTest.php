<?php

namespace Tests\Feature;

use App\Enums\DiscountType;
use App\Models\Category;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_belongs_to_category_and_has_option_groups_with_options(): void
    {
        $this->seed();

        $menu = MenuItem::where('slug', 'usus-ayam-crispy')->firstOrFail();

        $this->assertSame('Usus Ayam Crispy', $menu->category->name);

        $level = $menu->optionGroups->firstWhere('name', 'Level pedas');
        $this->assertTrue($level->is_required);
        $this->assertSame(['Original', 'Level 1', 'Level 3'], $level->options->pluck('name')->all());
        $this->assertSame(500, $level->options->last()->extra_price);
        $this->assertTrue($level->options->first()->group->is($level));
    }

    public function test_archived_menu_is_hidden_but_kept_in_database(): void
    {
        $this->seed();

        $menu = MenuItem::where('slug', 'basreng-original')->firstOrFail();
        $menu->delete();

        $this->assertNull(MenuItem::where('slug', 'basreng-original')->first());
        $this->assertNotNull(MenuItem::withTrashed()->where('slug', 'basreng-original')->first());
    }

    public function test_orderable_scope_excludes_unavailable_and_hidden_menus(): void
    {
        $this->seed();

        MenuItem::where('slug', 'basreng-original')->update(['is_visible' => false]);

        $orderable = MenuItem::orderable()->pluck('slug');

        $this->assertNotContains('makaroni-spiral-balado', $orderable, 'Habis tidak bisa dipesan');
        $this->assertNotContains('basreng-original', $orderable, 'Disembunyikan tidak bisa dipesan');
        $this->assertContains('usus-ayam-crispy', $orderable);
        $this->assertContains('makaroni-spiral-balado', MenuItem::visible()->pluck('slug'), 'Habis tetap tampil');
    }

    public function test_category_without_visible_menu_is_hidden(): void
    {
        $this->seed();

        Category::where('name', 'Makaroni')->firstOrFail()->menuItems()->update(['is_visible' => false]);

        $this->assertNotContains('Makaroni', Category::withVisibleMenus()->pluck('name'));
        $this->assertCount(3, Category::withVisibleMenus()->get());
    }

    public function test_morph_map_uses_short_aliases(): void
    {
        $this->assertSame(MenuItem::class, Relation::getMorphedModel('menu'));
        $this->assertSame(Category::class, Relation::getMorphedModel('category'));
        $this->assertSame(PromoCode::class, Relation::getMorphedModel('promo_code'));

        $this->seed();
        $discount = Discount::create([
            'name' => 'Diskon Basreng', 'type' => DiscountType::Percent, 'value' => 10, 'starts_at' => now(),
        ]);
        $menu = MenuItem::first();
        $category = Category::first();
        $discount->menuItems()->attach($menu);
        $discount->categories()->attach($category);

        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id, 'discountable_type' => 'menu', 'discountable_id' => $menu->id,
        ]);
        $this->assertDatabaseHas('discountables', [
            'discount_id' => $discount->id, 'discountable_type' => 'category', 'discountable_id' => $category->id,
        ]);
        $this->assertTrue($menu->discounts->first()->is($discount));
        $this->assertSame(DiscountType::Percent, $discount->fresh()->type);
    }

    public function test_promo_code_is_stored_uppercase(): void
    {
        $code = PromoCode::create([
            'code' => ' hemat5 ', 'type' => DiscountType::Fixed, 'value' => 5000, 'starts_at' => now(),
        ]);

        $this->assertSame('HEMAT5', $code->fresh()->code);
    }

    public function test_guest_order_keeps_snapshot_when_menu_is_deleted(): void
    {
        $this->seed();
        $menu = MenuItem::first();

        $order = Order::create([
            'code' => 'HC-0001',
            'customer_id' => null,
            'customer_name' => 'Dimas',
            'customer_phone' => '6281234567890',
            'address_snapshot' => ['text' => 'Jl. Melati No. 10, Bandung', 'notes' => 'pagar hijau'],
            'subtotal' => 36000,
            'promo_discount' => 0,
            'total_estimate' => 36000,
            'total_weight_grams' => 200,
            'idempotency_key' => (string) Str::uuid(),
        ]);
        $order->items()->create([
            'menu_item_id' => $menu->id,
            'name' => $menu->name,
            'base_price' => 18000,
            'unit_price' => 18000,
            'options' => [['group' => 'Level pedas', 'name' => 'Level 3', 'extra_price' => 500]],
            'options_extra' => 500,
            'quantity' => 2,
            'line_total' => 37000,
            'weight_grams' => 200,
        ]);

        $menu->forceDelete();

        $item = OrderItem::firstOrFail();
        $this->assertNull($item->menu_item_id, 'FK menu menjadi NULL, riwayat tetap ada');
        $this->assertSame('Usus Ayam Crispy', $item->name);
        $this->assertSame('Level 3', $item->options[0]['name']);
        $this->assertSame('Jl. Melati No. 10, Bandung', $order->fresh()->address_snapshot['text']);
        $this->assertCount(1, $order->fresh()->items);
    }
}
