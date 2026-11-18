<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\StoreSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        $store = $this->store();

        if (! $store->is_published) {
            return view('store.home', ['store' => $store, 'featured' => collect()]);
        }

        return view('store.home', [
            'store' => $store,
            'featured' => MenuItem::query()->visible()->where('is_featured', true)->orderBy('sort_order')->limit(8)->get(),
        ]);
    }

    public function about(): View
    {
        return view('store.about', ['store' => $this->publishedStore()]);
    }

    public function catalog(Request $request, ?string $slug = null): View
    {
        $store = $this->publishedStore();
        $categories = Category::query()->whereHas('menuItems', fn (Builder $query) => $query->visible())->orderBy('sort_order')->orderBy('name')->get();
        $category = $slug === null ? null : $categories->firstWhere('slug', $slug);
        abort_if($slug !== null && $category === null, 404);
        $search = $request->string('cari')->trim()->limit(80, '')->toString();

        $items = MenuItem::query()->visible()
            ->select('menu_items.*')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->whereNull('categories.deleted_at')
            ->when($category, fn (Builder $query) => $query->whereBelongsTo($category))
            ->when($search !== '', fn (Builder $query) => $query->whereLike('menu_items.name', '%'.$search.'%'))
            ->with('category')
            ->orderBy('categories.sort_order')->orderBy('categories.name')->orderBy('categories.id')
            ->orderBy('menu_items.sort_order')->orderBy('menu_items.name')->orderBy('menu_items.id')
            ->paginate(24)->withQueryString();

        $itemGroups = $items->getCollection()->groupBy('category_id');

        return view('store.catalog', compact('store', 'categories', 'category', 'items', 'itemGroups', 'search'));
    }

    public function item(string $slug): View
    {
        $store = $this->publishedStore();
        $item = MenuItem::query()->visible()->with('category')->where('slug', $slug)->firstOrFail();

        return view('store.item', compact('store', 'item'));
    }

    public function legal(Request $request): View
    {
        $store = $this->publishedStore();
        $privacy = $request->routeIs('store.privacy');
        $content = $privacy ? $store->privacy_policy : $store->terms;
        abort_if(blank($content), 404);

        return view('store.legal', [
            'store' => $store,
            'title' => $privacy ? 'Kebijakan Privasi' : 'Syarat & Ketentuan',
            'content' => $content,
        ]);
    }

    public function sitemap(): Response
    {
        $store = $this->publishedStore();
        $pages = collect([
            [route('home'), $store->updated_at],
            [route('store.about'), $store->updated_at],
            [route('store.catalog'), $store->updated_at],
        ]);

        Category::query()->whereHas('menuItems', fn (Builder $query) => $query->visible())
            ->each(fn (Category $category) => $pages->push([route('store.category', $category->slug), $category->updated_at]));
        MenuItem::query()->visible()
            ->each(fn (MenuItem $item) => $pages->push([route('store.item', $item->slug), $item->updated_at]));

        return response()->view('store.sitemap', ['pages' => $pages], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function store(): StoreSetting
    {
        return StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID);
    }

    private function publishedStore(): StoreSetting
    {
        $store = $this->store();
        abort_unless($store->is_published, 404);

        return $store;
    }
}
