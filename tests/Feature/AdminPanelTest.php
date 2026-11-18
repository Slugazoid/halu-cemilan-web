<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Livewire\Admin\Setup;
use App\Livewire\Admin\StaffAccounts;
use App\Livewire\Admin\StoreSettings;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_anonymous_and_storefront_accounts_cannot_open_the_admin_panel(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');

        $this->actingAs(Customer::factory()->create(), 'customer')
            ->get(route('admin.menu'))->assertRedirectToRoute('admin.login');

        $this->get(route('admin.login'))->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_owner_can_log_in_by_phone_and_must_change_a_temporary_password(): void
    {
        $owner = Admin::factory()->owner()->create([
            'phone' => '6281234567890',
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addDay(),
        ]);

        $this->post(route('admin.login.store'), ['identity' => '0812-3456-7890', 'password' => 'test-password-123'])
            ->assertRedirectToRoute('admin.dashboard');
        $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.password');

        $this->put(route('admin.password.update'), [
            'current_password' => 'test-password-123',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertRedirectToRoute('admin.dashboard');

        $this->assertFalse($owner->refresh()->must_change_password);
        $this->assertTrue(Hash::check('new-password-456', $owner->password));
        $this->assertGuest('web');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Lanjutkan setup');
        $this->post(route('admin.logout'))->assertRedirectToRoute('admin.login');
        $this->assertGuest('admin');
    }

    public function test_expired_temporary_password_is_rejected(): void
    {
        $staff = Admin::factory()->create([
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->subMinute(),
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['identity' => $staff->email, 'password' => 'test-password-123'])
            ->assertRedirectToRoute('admin.login')
            ->assertSessionHasErrors('identity');

        $this->assertGuest('admin');
    }

    public function test_staff_can_open_catalog_but_cannot_access_owner_sections(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->get(route('admin.menu'))->assertOk()->assertSeeLivewire(Catalog::class)->assertDontSee('Identitas &amp; tampilan', false);

        foreach (['setup', 'profil', 'tampilan', 'pengaturan', 'seo', 'promo', 'akun-admin'] as $section) {
            $this->get(route('admin.'.$section))->assertForbidden();
        }

        Livewire::test(StoreSettings::class, ['section' => 'profil'])->assertForbidden();
    }

    public function test_owner_staff_reset_revokes_the_previous_session_and_keeps_an_audit_record(): void
    {
        $owner = Admin::factory()->owner()->create();
        $staff = Admin::factory()->create();
        $previousSessionHash = hash('sha256', $staff->getAuthPassword());
        $this->actingAs($owner, 'admin');

        Livewire::test(StaffAccounts::class)
            ->call('resetStaffPassword', $staff->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activity_logs', ['admin_id' => $owner->id, 'action' => 'admin.password_reset', 'subject_type' => 'admin', 'subject_id' => $staff->id]);
        $this->assertTrue($staff->refresh()->must_change_password);

        $this->actingAs($staff, 'admin')->withSession(['admin.password_hash' => $previousSessionHash])
            ->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
        $this->assertGuest('admin');
    }

    public function test_setup_requires_a_visible_available_menu_and_publishes_saved_store_content(): void
    {
        $this->actingAs(Admin::factory()->owner()->create(), 'admin');

        $this->get(route('home'))->assertOk()->assertSee('Segera hadir')->assertSee('noindex, nofollow');
        $this->get(route('store.catalog'))->assertNotFound();

        Livewire::test(Setup::class)->call('publish')->assertHasErrors('publish');

        Livewire::test(StoreSettings::class, ['section' => 'profil'])
            ->set('fields.name', 'Toko Contoh')
            ->set('fields.description', 'Cerita toko')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.profil'));

        Livewire::test(StoreSettings::class, ['section' => 'pengaturan'])
            ->set('fields.whatsapp_number', '0812 3456 7890')
            ->set('fields.privacy_policy', 'Kebijakan privasi toko.')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(StoreSettings::class, ['section' => 'tampilan'])
            ->set('fields.primary_color', '#004080')
            ->set('fields.accent_color', '#F09000')
            ->set('fields.theme', 'hangat')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.tampilan'));

        Livewire::test(Setup::class)->call('publish')->assertHasErrors('publish');

        Livewire::test(Catalog::class)->set('category.name', 'Basreng')->call('saveCategory')->assertHasNoErrors();
        $category = Category::query()->sole();

        Livewire::test(Catalog::class)
            ->set('item.category_id', $category->id)
            ->set('item.name', 'Basreng Pedas')
            ->set('item.price', 12000)
            ->set('item.weight_grams', 100)
            ->set('item.is_featured', true)
            ->call('saveItem')
            ->assertHasNoErrors();

        $item = MenuItem::query()->sole();
        Livewire::test(Setup::class)->call('publish')->assertHasNoErrors()->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('store_settings', ['id' => StoreSetting::SINGLETON_ID, 'is_published' => true, 'whatsapp_number' => '6281234567890']);
        $this->get(route('home'))->assertOk()->assertSee('Toko Contoh')->assertSee('Basreng Pedas')
            ->assertSee('--store-primary: #004080')->assertSee('--store-accent: #F09000')->assertSee('bg-amber-50/40');
        $this->get(route('store.privacy'))->assertOk()->assertSee('Kebijakan privasi toko.');
        $this->get(route('store.catalog'))->assertOk()->assertSee('Basreng Pedas');
        $this->get(route('store.category', $category->slug))->assertOk()->assertSee('Basreng Pedas');
        $this->get(route('store.item', $item->slug))->assertOk()->assertSee('Rp 12.000');
        $this->get(route('store.sitemap'))->assertOk()->assertSee(route('store.item', $item->slug));
        $this->get(route('admin.setup'))->assertRedirectToRoute('admin.dashboard');
    }

    public function test_public_catalog_groups_visible_items_in_category_order_and_searches_by_name(): void
    {
        StoreSetting::query()->sole()->forceFill(['name' => 'Toko', 'is_published' => true])->save();
        $snacks = Category::query()->create(['name' => 'Camilan', 'slug' => 'camilan', 'sort_order' => 2]);
        $drinks = Category::query()->create(['name' => 'Minuman', 'slug' => 'minuman', 'sort_order' => 1]);

        $snacks->menuItems()->create(['name' => 'Basreng Pedas', 'slug' => 'basreng-pedas', 'price' => 15000, 'weight_grams' => 100]);
        $drinks->menuItems()->create(['name' => 'Es Teh', 'slug' => 'es-teh', 'price' => 5000, 'weight_grams' => 200]);
        $drinks->menuItems()->create(['name' => 'Menu Rahasia', 'slug' => 'menu-rahasia', 'price' => 5000, 'weight_grams' => 200, 'is_visible' => false]);

        $this->get(route('store.catalog'))->assertOk()
            ->assertSeeInOrder(['>Minuman</h2>', 'Es Teh', '>Camilan</h2>', 'Basreng Pedas'], false)
            ->assertDontSee('Menu Rahasia');

        $this->get(route('store.catalog', ['cari' => 'Basreng']))->assertOk()
            ->assertSee('Basreng Pedas')->assertDontSee('Es Teh');

        $this->get(route('store.category', $drinks->slug))->assertOk()
            ->assertSee('Es Teh')->assertDontSee('Basreng Pedas');
    }

    public function test_sold_out_menu_remains_public_until_archived(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');
        StoreSetting::query()->sole()->forceFill(['name' => 'Toko', 'is_published' => true])->save();
        $category = Category::query()->create(['name' => 'Makaroni', 'slug' => 'makaroni']);
        $item = MenuItem::query()->create([
            'category_id' => $category->id,
            'name' => 'Makaroni Balado',
            'slug' => 'makaroni-balado',
            'price' => 7000,
            'weight_grams' => 80,
        ]);

        Livewire::test(Catalog::class)->call('toggleAvailability', $item->id)->assertHasNoErrors();
        $this->get(route('store.item', $item->slug))->assertOk()->assertSee('Habis');

        Livewire::test(Catalog::class)->call('archiveCategory', $category->id)->assertHasErrors('archive');
        Livewire::test(Catalog::class)->call('archiveItem', $item->id)->assertHasNoErrors();

        $this->assertSoftDeleted($item);
        $this->get(route('store.item', $item->slug))->assertNotFound();
        $this->get(route('store.category', $category->slug))->assertNotFound();
        $this->get(route('store.sitemap'))->assertOk()->assertDontSee($item->slug);
    }
}
