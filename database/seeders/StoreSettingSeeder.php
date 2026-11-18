<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use App\Support\PhoneNumber;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        $store = MockData::get('store');

        StoreSetting::query()->updateOrCreate(['id' => 1], [
            'name' => $store['name'],
            'tagline' => $store['tagline'],
            'description' => 'Placeholder cerita usaha. Ganti dengan cerita asli mitra.',
            'primary_color' => $store['primary_color'],
            'accent_color' => $store['accent_color'],
            'font' => $store['font'],
            'theme' => 'netral',
            'address' => 'Alamat placeholder, '.$store['city'],
            'city' => $store['city'],
            'whatsapp_number' => PhoneNumber::normalize($store['whatsapp_number']),
            'opening_hours' => $store['opening_hours'],
            'shipping_info' => $store['shipping_info'],
            'message_greeting' => 'Halo '.$store['name'].', saya mau pesan',
            'message_closing' => 'Terima kasih!',
            'order_code_prefix' => $store['order_code_prefix'],
            'meta_title' => $store['name'].' — '.$store['city'],
            'meta_description' => $store['tagline'],
            'domain' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost',
            // Mock sudah punya nama, nomor WA, dan menu Tersedia (BR-32).
            'is_published' => true,
        ]);
    }
}
