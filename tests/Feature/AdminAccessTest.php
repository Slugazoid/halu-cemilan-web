<?php

namespace Tests\Feature;

use App\Livewire\Admin\Catalog;
use App\Livewire\Admin\Setup;
use App\Livewire\Admin\StaffAccounts;
use App\Livewire\Admin\StoreSettings;
use App\Models\Admin;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Matriks akses dashboard (BR-34): Staf dan Pemilik sama-sama mengelola menu, kategori, slug, meta SEO,
 * foto, varian, label, unggulan, dan urutan. Bagian toko (setup, profil, tampilan, pengaturan, SEO toko,
 * promo, akun admin) hanya untuk Pemilik.
 */
class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const array OWNER_ONLY = ['setup', 'promo', 'profil', 'tampilan', 'pengaturan', 'seo', 'akun-admin'];

    private const array SHARED = ['pesanan', 'menu', 'pelanggan', 'banner', 'statistik'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @return array<string, array{string}> */
    public static function roles(): array
    {
        return ['staf' => ['staff'], 'pemilik' => ['owner']];
    }

    private function loginAs(string $role): Admin
    {
        $admin = $role === 'owner' ? Admin::factory()->owner()->create() : Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    #[DataProvider('roles')]
    public function test_both_roles_manage_the_whole_catalog(string $role): void
    {
        $this->loginAs($role);

        Livewire::test(Catalog::class)
            ->set('category.name', 'Basreng')
            ->set('category.meta_title', 'Basreng Bandung')
            ->call('saveCategory')
            ->assertHasNoErrors();
        $category = Category::query()->sole();
        $this->assertSame('Basreng Bandung', $category->meta_title);

        Livewire::test(Catalog::class)
            ->set('item.category_id', $category->id)
            ->set('item.name', 'Basreng Pedas')
            ->set('item.slug', 'basreng-pedas')
            ->set('item.price', 12000)
            ->set('item.weight_grams', 100)
            ->set('item.meta_title', 'Basreng Pedas Bandung')
            ->set('item.labels', ['Pedas'])
            ->set('item.is_featured', true)
            ->call('addGroup')
            ->set('groups.0.name', 'Ukuran')
            ->set('groups.0.options.0.name', '100 g')
            ->call('saveItem')
            ->assertHasNoErrors();

        $menu = MenuItem::query()->sole();
        $this->assertSame('Basreng Pedas Bandung', $menu->meta_title);
        $this->assertSame(['Pedas'], $menu->labels);
        $this->assertTrue($menu->is_featured);
        $this->assertSame(1, $menu->optionGroups()->count());

        Livewire::test(Catalog::class)
            ->call('editItem', $menu->id)
            ->set('item.slug', 'basreng-super-pedas')
            ->call('saveItem')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('redirects', ['old_path' => '/menu/basreng-pedas', 'new_path' => '/menu/basreng-super-pedas', 'status_code' => 301]);

        Livewire::test(Catalog::class)
            ->call('toggleFeatured', $menu->id)
            ->call('toggleAvailability', $menu->id)
            ->call('sortItem', $menu->id, 0)
            ->call('sortCategory', $category->id, 0)
            ->assertHasNoErrors();
        $this->assertFalse($menu->refresh()->is_featured);
        $this->assertFalse($menu->is_available);

        Livewire::test(Catalog::class)->call('archiveItem', $menu->id)->assertHasNoErrors();
        $this->assertSoftDeleted($menu);
    }

    #[DataProvider('roles')]
    public function test_both_roles_open_the_operational_sections(string $role): void
    {
        $this->loginAs($role);

        foreach (self::SHARED as $section) {
            $this->get(route('admin.'.$section))->assertOk();
        }

        $this->get(route('admin.menu'))->assertSeeLivewire(Catalog::class);
    }

    public function test_staff_is_blocked_from_every_owner_section_and_does_not_see_them_in_the_sidebar(): void
    {
        $this->loginAs('staff');

        foreach (self::OWNER_ONLY as $section) {
            $this->get(route('admin.'.$section))->assertForbidden();
        }

        $page = $this->get(route('admin.menu'));

        foreach (self::OWNER_ONLY as $section) {
            $page->assertDontSee(e(config('admin.sections.'.$section.'.label')), false);
        }
    }

    public function test_every_section_marked_owner_only_in_config_is_blocked_for_staff(): void
    {
        $this->loginAs('staff');

        $ownerSections = collect(config('admin.sections'))->filter(fn (array $page): bool => $page['owner'])->keys()->sort()->values()->all();

        $this->assertSame(collect(self::OWNER_ONLY)->sort()->values()->all(), $ownerSections);
    }

    public function test_staff_cannot_use_owner_only_components(): void
    {
        $this->loginAs('staff');

        Livewire::test(Setup::class)->assertForbidden();
        Livewire::test(StaffAccounts::class)->assertForbidden();

        foreach (['profil', 'tampilan', 'pengaturan', 'seo'] as $section) {
            Livewire::test(StoreSettings::class, ['section' => $section])->assertForbidden();
        }
    }

    public function test_owner_can_use_owner_only_components(): void
    {
        $this->loginAs('owner');

        Livewire::test(Setup::class)->assertOk();
        Livewire::test(StaffAccounts::class)->assertOk();
        Livewire::test(StoreSettings::class, ['section' => 'profil'])->assertOk();
    }

    public function test_inactive_admin_is_sent_back_to_login_and_loses_owner_rights(): void
    {
        $owner = Admin::factory()->owner()->create(['is_active' => false]);

        $this->actingAs($owner, 'admin')->get(route('admin.menu'))->assertRedirectToRoute('admin.login');
        $this->assertTrue(Gate::forUser($owner)->denies('admin-owner'));
    }

    public function test_owner_role_cannot_be_granted_by_mass_assignment(): void
    {
        $admin = new Admin;
        $admin->fill(['name' => 'Staf Baru', 'role' => 'owner', 'is_active' => false, 'owner_slot' => true]);

        $this->assertNull($admin->role);
        $this->assertFalse($admin->isOwner());
    }
}
