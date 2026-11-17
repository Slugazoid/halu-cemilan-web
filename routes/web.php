<?php

use App\Http\Controllers\AdminPasswordController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\PreventAdminIndexing;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/tentang', [StorefrontController::class, 'about'])->name('store.about');
Route::get('/menu', [StorefrontController::class, 'catalog'])->name('store.catalog');
Route::get('/menu/kategori/{slug}', [StorefrontController::class, 'catalog'])->name('store.category');
Route::get('/menu/{slug}', [StorefrontController::class, 'item'])->name('store.item');
Route::get('/kebijakan-privasi', [StorefrontController::class, 'legal'])->name('store.privacy');
Route::get('/syarat-ketentuan', [StorefrontController::class, 'legal'])->name('store.terms');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('store.sitemap');

Route::prefix('admin')->name('admin.')->middleware(PreventAdminIndexing::class)->group(function (): void {
    Route::get('/masuk', [AdminSessionController::class, 'create'])->name('login');
    Route::post('/masuk', [AdminSessionController::class, 'store'])->middleware('throttle:30,1')->name('login.store');

    Route::middleware(EnsureAdminAccess::class)->group(function (): void {
        Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');
        Route::post('/keluar', [AdminSessionController::class, 'destroy'])->name('logout');
        Route::view('/kata-sandi', 'admin.password')->name('password');
        Route::put('/kata-sandi', [AdminPasswordController::class, 'update'])->name('password.update');

        foreach (config('admin.sections') as $section => $page) {
            $route = Route::view('/'.$section, $page['view'], ['title' => $page['label'], 'section' => $section])->name($section);

            if ($page['owner']) {
                $route->can('admin-owner');
            }
        }
    });
});
