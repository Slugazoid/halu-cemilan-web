<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\StoreSetting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_store_from_mock_data(): void
    {
        $this->seed();

        $store = StoreSetting::current();

        $this->assertSame('Halu Cemilan Instan', $store->name);
        $this->assertSame('HC', $store->order_code_prefix);
        $this->assertSame(0, $store->order_sequence);
        $this->assertMatchesRegularExpression('/^628\d{8,13}$/', $store->whatsapp_number);
        $this->assertTrue($store->is_published);
        $this->assertStringContainsString('Senin–Sabtu', $store->opening_hours);
        $this->assertSame(1, StoreSetting::count(), 'store_settings harus tepat satu baris');
    }

    public function test_seeds_catalog_with_slugs_options_and_unavailable_item(): void
    {
        $this->seed();

        $this->assertSame(4, Category::count());
        $this->assertSame(6, MenuItem::count());
        $this->assertSame(8, MenuOption::count());

        $usus = MenuItem::where('slug', 'usus-ayam-crispy')->firstOrFail();
        $this->assertSame(18000, $usus->price);
        $this->assertCount(2, $usus->optionGroups);

        $makaroni = MenuItem::where('name', 'Makaroni Spiral Balado')->firstOrFail();
        $this->assertFalse($makaroni->is_available);
        $this->assertTrue($makaroni->is_visible);
    }

    public function test_seeds_one_owner_who_must_change_password(): void
    {
        $this->seed();

        $owner = Admin::firstOrFail();

        $this->assertSame('owner', $owner->role);
        $this->assertTrue($owner->isOwner());
        $this->assertTrue($owner->owner_slot);
        $this->assertTrue($owner->must_change_password);
        $this->assertTrue($owner->is_active);
        $this->assertTrue(Hash::isHashed($owner->password));
        $this->assertSame(1, Admin::where('role', 'owner')->count());
    }

    public function test_seeds_faq_and_testimonial(): void
    {
        $this->seed();

        $this->assertSame(2, Faq::count());
        $this->assertSame(1, Testimonial::count());
    }

    public function test_seeding_twice_does_not_duplicate_data(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, StoreSetting::count());
        $this->assertSame(1, Admin::count());
        $this->assertSame(4, Category::count());
        $this->assertSame(6, MenuItem::count());
        $this->assertSame(8, MenuOption::count());
        $this->assertSame(2, Faq::count());
    }
}
