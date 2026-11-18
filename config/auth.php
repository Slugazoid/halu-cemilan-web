<?php

use App\Models\Admin;
use App\Models\Customer;

/*
 * Dua guard terpisah supaya sesi admin dan pelanggan terisolasi (PRD bagian 10):
 *   - admin    : dashboard /admin, login dengan nomor HP, aktif sejak Fase 1.
 *   - customer : akun pelanggan, rute baru didaftarkan bila
 *                features.customer_accounts = true (lihat config/features.php).
 *
 * Reset kata sandi memakai OTP buatan sendiri (BR-54), bukan password broker
 * bawaan Laravel, jadi bagian "passwords" sengaja tidak ada.
 */
return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'admin'),
    ],

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
        'customers' => [
            'driver' => 'eloquent',
            'model' => Customer::class,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
