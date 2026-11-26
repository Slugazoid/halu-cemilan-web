<?php

use App\Http\Controllers\AdminPasswordController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\RegionLookupController;
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

Route::prefix('api/wilayah')->name('regions.')->middleware('throttle:60,1')->group(function (): void {
    Route::get('/provinsi', [RegionLookupController::class, 'provinces'])->name('provinces');
    Route::get('/provinsi/{provinceCode}/kabupaten', [RegionLookupController::class, 'regencies'])
        ->where('provinceCode', '[0-9]{2}')->name('regencies');
    Route::get('/kabupaten/{regencyCode}/kecamatan', [RegionLookupController::class, 'districts'])
        ->where('regencyCode', '[0-9]{4}')->name('districts');
    Route::get('/kecamatan/{districtCode}/kelurahan', [RegionLookupController::class, 'villages'])
        ->where('districtCode', '[0-9]{6}')->name('villages');
});

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
