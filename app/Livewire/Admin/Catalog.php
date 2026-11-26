<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\StoreSetting;
use App\Services\ImageService;
use App\Services\RedirectService;
use App\Support\SlugGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Catalog extends Component
{
    use WithFileUploads, WithPagination;

    private const array ITEM_DEFAULTS = [
        'category_id' => '', 'name' => '', 'slug' => '', 'description' => '', 'price' => '', 'weight_grams' => '',
        'photo_alt' => '', 'labels' => [], 'ingredients' => '', 'shelf_life' => '', 'sort_order' => 0,
        'is_visible' => true, 'is_available' => true, 'is_featured' => false,
    ];

    public array $item = self::ITEM_DEFAULTS;

    public array $category = ['name' => '', 'slug' => '', 'description' => '', 'sort_order' => 0];

    /** Foto baru yang dipilih di form menu (belum disimpan). */
    public $photo = null;

    #[Locked]
    public ?int $editingItem = null;

    #[Locked]
    public ?int $editingCategory = null;

    public function boot(): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin?->is_active && ! $admin->must_change_password, 403);
    }

    public function editItem(int $id): void
    {
        $this->resetValidation();
        $this->editingItem = $id;
        $this->item = MenuItem::query()->findOrFail($id)->only(array_keys(self::ITEM_DEFAULTS));
        $this->item['labels'] = array_values($this->item['labels'] ?? []);
    }

    public function saveItem(): void
    {
        $validated = $this->validate([
            'item' => ['required', 'array:'.implode(',', array_keys(self::ITEM_DEFAULTS))],
            'item.name' => ['required', 'string', 'max:80'],
            'item.slug' => ['nullable', 'string', 'max:60'],
            'item.photo_alt' => ['nullable', 'string', 'max:125'],
            'item.labels' => ['nullable', 'array'],
            'item.labels.*' => ['string', Rule::in(MenuItem::LABELS)],
            'photo' => ['nullable', ...ImageService::rules()],
            'item.category_id' => ['required', 'integer', Rule::exists('categories', 'id')->withoutTrashed()],
            'item.price' => ['required', 'integer', 'between:1,4294967295'],
            'item.weight_grams' => ['required', 'integer', 'between:1,65535'],
            'item.description' => ['nullable', 'string', 'max:5000'],
            'item.ingredients' => ['nullable', 'string', 'max:2000'],
            'item.shelf_life' => ['nullable', 'string', 'max:50'],
            'item.sort_order' => ['required', 'integer', 'between:0,99999'],
            'item.is_visible' => ['required', 'boolean'],
            'item.is_available' => ['required', 'boolean'],
            'item.is_featured' => ['required', 'boolean'],
        ]);
        $data = $validated['item'];
        $data['photo_alt'] = filled($data['photo_alt'] ?? null) ? trim($data['photo_alt']) : null;
        // Label disimpan berurutan sesuai daftar resmi; tanpa label = null.
        $data['labels'] = array_values(array_intersect(MenuItem::LABELS, $data['labels'] ?? [])) ?: null;

        $redirects = app(RedirectService::class);

        $item = DB::transaction(function () use ($data, $redirects): MenuItem {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);
            Category::query()->findOrFail($data['category_id']);
            $item = $this->editingItem === null ? new MenuItem : MenuItem::query()->findOrFail($this->editingItem);

            if ($data['is_featured'] && ! $item->is_featured && MenuItem::query()->where('is_featured', true)->count() >= 8) {
                throw ValidationException::withMessages(['item.is_featured' => 'Maksimal delapan menu unggulan.']);
            }

            // Slug: kosong = otomatis dari nama; diubah = simpan alamat lama sebagai redirect 301 (BR-49, BR-50).
            $oldSlug = $item->exists ? $item->slug : null;
            $slug = trim((string) ($data['slug'] ?? ''));
            unset($data['slug']);

            if (! $item->exists) {
                $item->slug = SlugGenerator::unique(MenuItem::class, $slug !== '' ? $slug : $data['name']);
            } elseif ($slug !== '' && $slug !== $oldSlug) {
                $item->slug = SlugGenerator::unique(MenuItem::class, $slug, $item->id);
            }

            $item->fill($data)->save();

            if ($oldSlug !== null && $item->slug !== $oldSlug) {
                $redirects->record('/menu/'.$oldSlug, '/menu/'.$item->slug);
            }

            return $item;
        });

        // Foto dikonversi ke WebP di belakang layar (queue); kolom photo_path terisi setelah job selesai.
        $hasNewPhoto = $this->photo !== null;

        if ($hasNewPhoto) {
            app(ImageService::class)->queue($item, 'photo_path', $this->photo, $item->slug, 'menu');
        }

        $this->cancelItem();
        session()->flash('status', $hasNewPhoto ? 'Menu disimpan. Foto sedang diproses.' : 'Menu disimpan.');
    }

    public function cancelItem(): void
    {
        $this->reset('item', 'editingItem', 'photo');
        $this->resetValidation();
    }

    public function removePhoto(): void
    {
        abort_if($this->editingItem === null, 404);

        DB::transaction(function (): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);
            $item = MenuItem::query()->findOrFail($this->editingItem);

            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
                $item->forceFill(['photo_path' => null])->save();
            }
        });

        $this->reset('photo');
        session()->flash('status', 'Foto dihapus.');
    }

    public function toggleAvailability(int $id): void
    {
        DB::transaction(function () use ($id): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);
            $item = MenuItem::query()->findOrFail($id);
            $item->update(['is_available' => ! $item->is_available]);

            if ($this->editingItem === $id) {
                $this->item['is_available'] = $item->is_available;
            }
        });
    }

    public function archiveItem(int $id): void
    {
        DB::transaction(function () use ($id): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);
            MenuItem::query()->findOrFail($id)->delete();
        });

        if ($this->editingItem === $id) {
            $this->cancelItem();
        }

        $this->resetPage();
        session()->flash('status', 'Menu diarsipkan.');
    }

    public function editCategory(int $id): void
    {
        $this->resetValidation();
        $this->editingCategory = $id;
        $this->category = Category::query()->findOrFail($id)->only(['name', 'slug', 'description', 'sort_order']);
    }

    public function saveCategory(): void
    {
        $data = $this->validate([
            'category' => ['required', 'array:name,slug,description,sort_order'],
            'category.name' => ['required', 'string', 'max:60'],
            'category.slug' => ['nullable', 'string', 'max:60'],
            'category.description' => ['nullable', 'string', 'max:5000'],
            'category.sort_order' => ['required', 'integer', 'between:0,99999'],
        ])['category'];

        $redirects = app(RedirectService::class);

        DB::transaction(function () use ($data, $redirects): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);

            $slug = trim((string) ($data['slug'] ?? ''));
            unset($data['slug']);

            if ($this->editingCategory === null) {
                $data['slug'] = SlugGenerator::unique(Category::class, $slug !== '' ? $slug : $data['name']);
                Category::query()->create($data);
            } else {
                $category = Category::query()->findOrFail($this->editingCategory);
                $oldSlug = $category->slug;

                if ($slug !== '' && $slug !== $oldSlug) {
                    $data['slug'] = SlugGenerator::unique(Category::class, $slug, $category->id);
                }

                $category->update($data);

                if ($category->slug !== $oldSlug) {
                    $redirects->record('/menu/kategori/'.$oldSlug, '/menu/kategori/'.$category->slug);
                }
            }
        });

        $this->cancelCategory();
        session()->flash('status', 'Kategori disimpan.');
    }

    public function cancelCategory(): void
    {
        $this->reset('category', 'editingCategory');
        $this->resetValidation();
    }

    public function archiveCategory(int $id): void
    {
        DB::transaction(function () use ($id): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);
            $category = Category::query()->findOrFail($id);

            if ($category->menuItems()->exists()) {
                throw ValidationException::withMessages(['archive' => 'Pindahkan atau arsipkan menu dalam kategori ini terlebih dahulu.']);
            }

            $category->delete();
        });

        if ($this->editingCategory === $id) {
            $this->cancelCategory();
        }

        session()->flash('status', 'Kategori diarsipkan.');
    }

    public function render(): View
    {
        return view('livewire.admin.catalog', [
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(),
            'items' => MenuItem::query()->with('category')->orderBy('sort_order')->orderBy('id')->paginate(12),
            'currentPhoto' => $this->editingItem ? MenuItem::query()->find($this->editingItem)?->photo_url : null,
        ]);
    }
}
