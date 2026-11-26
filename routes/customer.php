<?php

use App\Http\Controllers\Customer\CustomerAddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('profil/alamat')->name('customer.addresses.')->middleware('auth:customer')->group(function (): void {
    Route::get('/', [CustomerAddressController::class, 'index'])->name('index');
    Route::get('/baru', [CustomerAddressController::class, 'create'])->name('create');
    Route::post('/', [CustomerAddressController::class, 'store'])->name('store');
    Route::get('/{address}/ubah', [CustomerAddressController::class, 'edit'])->whereNumber('address')->name('edit');
    Route::put('/{address}', [CustomerAddressController::class, 'update'])->whereNumber('address')->name('update');
    Route::patch('/{address}/utama', [CustomerAddressController::class, 'makePrimary'])->whereNumber('address')->name('primary');
    Route::delete('/{address}', [CustomerAddressController::class, 'destroy'])->whereNumber('address')->name('destroy');
});
