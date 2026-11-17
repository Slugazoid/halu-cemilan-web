<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\StoreSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Catalog extends Component
{
    use WithPagination;

    private const array ITEM_DEFAULTS = [
        'category_id' => '', 'name' => '', 'description' => '', 'price' => '', 'weight_grams' => '',
        'ingredients' => '', 'shelf_life' => '', 'sort_order' => 0,
        'is_visible' => true, 'is_available' => true, 'is_featured' => false,
    ];

    public array $item = self::ITEM_DEFAULTS;

    public array $category = ['name' => '', 'description' => '', 'sort_order' => 0];

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
    }

    public function saveItem(): void
    {
        $data = $this->validate([
            'item' => ['required', 'array:'.implode(',', array_keys(self::ITEM_DEFAULTS))],
            'item.name' => ['required', 'string', 'max:80'],
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
        ])['item'];

        DB::transaction(function () use ($data): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);
            Category::query()->findOrFail($data['category_id']);
            $item = $this->editingItem === null ? new MenuItem : MenuItem::query()->findOrFail($this->editingItem);

            if ($data['is_featured'] && ! $item->is_featured && MenuItem::query()->where('is_featured', true)->count() >= 8) {
                throw ValidationException::withMessages(['item.is_featured' => 'Maksimal delapan menu unggulan.']);
            }

            if (! $item->exists) {
                $data['slug'] = $this->uniqueSlug(MenuItem::withTrashed(), $data['name'], 'menu');
            }

            $item->fill($data)->save();
        });

        $this->cancelItem();
        session()->flash('status', 'Menu disimpan.');
    }

    public function cancelItem(): void
    {
        $this->reset('item', 'editingItem');
        $this->resetValidation();
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
        $this->category = Category::query()->findOrFail($id)->only(['name', 'description', 'sort_order']);
    }

    public function saveCategory(): void
    {
        $data = $this->validate([
            'category' => ['required', 'array:name,description,sort_order'],
            'category.name' => ['required', 'string', 'max:60'],
            'category.description' => ['nullable', 'string', 'max:5000'],
            'category.sort_order' => ['required', 'integer', 'between:0,99999'],
        ])['category'];

        DB::transaction(function () use ($data): void {
            StoreSetting::query()->lockForUpdate()->findOrFail(StoreSetting::SINGLETON_ID);

            if ($this->editingCategory === null) {
                $data['slug'] = $this->uniqueSlug(Category::withTrashed(), $data['name'], 'kategori');
                Category::query()->create($data);
            } else {
                Category::query()->findOrFail($this->editingCategory)->update($data);
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
        ]);
    }

    /** @param Builder<Category|MenuItem> $query */
    private function uniqueSlug(Builder $query, string $name, string $fallback): string
    {
        $base = Str::slug($name) ?: $fallback;
        $slug = rtrim(substr($base, 0, 60), '-');

        for ($index = 2; (clone $query)->where('slug', $slug)->exists(); $index++) {
            $suffix = '-'.$index;
            $slug = rtrim(substr($base, 0, 60 - strlen($suffix)), '-').$suffix;
        }

        return $slug;
    }
}
