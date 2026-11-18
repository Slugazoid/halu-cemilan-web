<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

/** Satu akun Pemilik (BR-33). Wajib ganti kata sandi saat masuk pertama. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_OWNER_PASSWORD');

        if (! $password) {
            if (app()->isProduction()) {
                throw new RuntimeException('Isi SEED_OWNER_PASSWORD di .env sebelum seeding di production.');
            }
            $password = 'GantiSandi123';
        }

        if (Admin::query()->where('owner_slot', true)->exists()) {
            return;
        }

        // role/owner_slot tidak mass-assignable, jadi pakai forceFill.
        (new Admin([
            'name' => 'Pemilik',
            'phone' => '6281200000001',          // placeholder, bukan nomor asli mitra
            'password' => $password,             // di-hash oleh cast "hashed"
        ]))->forceFill([
            'role' => AdminRole::Owner->value,
            'owner_slot' => true,
            'is_active' => true,
            'must_change_password' => true,      // tanpa batas waktu agar nyaman di dev
        ])->save();
    }
}
