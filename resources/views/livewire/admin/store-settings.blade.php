<div>
    <x-admin.card>
        <form wire:submit="save" class="grid max-w-2xl gap-4">
            @php
                $labels = [
                    'name' => 'Nama toko', 'tagline' => 'Tagline', 'description' => 'Cerita usaha',
                    'address' => 'Alamat toko', 'city' => 'Kota', 'maps_url' => 'Tautan Google Maps (HTTPS)',
                    'primary_color' => 'Warna utama', 'accent_color' => 'Warna aksen', 'theme' => 'Tema',
                    'whatsapp_number' => 'Nomor WhatsApp pesanan', 'opening_hours' => 'Jam buka',
                    'shipping_info' => 'Informasi pengiriman', 'order_code_prefix' => 'Awalan kode pesanan (2–3 huruf kapital)',
                    'message_greeting' => 'Salam pembuka WhatsApp', 'message_closing' => 'Salam penutup WhatsApp',
                    'privacy_policy' => 'Kebijakan Privasi', 'terms' => 'Syarat & Ketentuan',
                    'meta_title' => 'Judul halaman (maks. 70 karakter)', 'meta_description' => 'Deskripsi halaman (maks. 170 karakter)',
                    'gsc_verification' => 'Kode verifikasi Google Search Console',
                ];
            @endphp
            @foreach ($fieldNames as $field)
                <x-admin.field :name="'fields.'.$field" :label="$labels[$field]" wire:key="setting-{{ $field }}">
                    @if ($field === 'theme')
                        <select id="fields.{{ $field }}" wire:model="fields.{{ $field }}" class="admin-input">
                            <option value="netral">Netral</option>
                            <option value="hangat">Hangat</option>
                            <option value="kontras">Kontras</option>
                        </select>
                    @elseif (in_array($field, ['description', 'address', 'opening_hours', 'shipping_info', 'privacy_policy', 'terms', 'meta_description'], true))
                        <textarea id="fields.{{ $field }}" wire:model="fields.{{ $field }}" class="admin-input" rows="3"></textarea>
                    @elseif (in_array($field, ['primary_color', 'accent_color'], true))
                        <input id="fields.{{ $field }}" wire:model="fields.{{ $field }}" class="admin-input w-28" type="color">
                    @else
                        <input id="fields.{{ $field }}" wire:model="fields.{{ $field }}" class="admin-input"
                            type="{{ match ($field) { 'whatsapp_number' => 'tel', 'maps_url' => 'url', default => 'text' } }}">
                    @endif
                </x-admin.field>
            @endforeach
            <button class="admin-button admin-button-primary justify-self-start">Simpan perubahan</button>
        </form>
    </x-admin.card>
</div>
