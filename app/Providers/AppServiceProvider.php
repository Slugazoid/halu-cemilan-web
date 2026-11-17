<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdminAccess;
use App\Models\Admin;
use App\Models\StoreSetting;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin-owner', fn (Authenticatable $user): bool => $user instanceof Admin && $user->is_active && $user->isOwner());
        Livewire::addPersistentMiddleware(EnsureAdminAccess::class);

        View::composer('admin.layout', function (\Illuminate\View\View $view): void {
            $store = StoreSetting::query()->findOrFail(StoreSetting::SINGLETON_ID);
            $view->with(['storeName' => $store->name, 'storePublished' => $store->is_published]);
        });
    }
}
