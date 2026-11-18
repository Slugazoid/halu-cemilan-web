<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdminAccess;
use App\Models\Admin;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\StoreSetting;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Alias pendek untuk kolom polimorfik (*_type VARCHAR(20)).
        Relation::enforceMorphMap([
            'admin' => Admin::class,
            'customer' => Customer::class,
            'menu' => MenuItem::class,
            'category' => Category::class,
            'discount' => Discount::class,
            'promo_code' => PromoCode::class,
            'banner' => Banner::class,
            'order' => Order::class,
            'store' => StoreSetting::class,
        ]);

        Gate::define('admin-owner', fn (Authenticatable $user): bool => $user instanceof Admin && $user->is_active && $user->isOwner());
        Livewire::addPersistentMiddleware(EnsureAdminAccess::class);

        View::composer('admin.layout', function (\Illuminate\View\View $view): void {
            $store = StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID);
            $view->with(['storeName' => $store->name, 'storePublished' => $store->is_published]);
        });
    }
}
