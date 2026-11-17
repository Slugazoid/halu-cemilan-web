<?php

return [
    'sections' => [
        'setup' => ['label' => 'Setup awal', 'group' => 'Toko', 'icon' => 'checklist', 'owner' => true, 'view' => 'admin.setup'],
        'pesanan' => ['label' => 'Pesanan', 'group' => 'Operasional', 'icon' => 'bag', 'owner' => false, 'view' => 'admin.pending'],
        'menu' => ['label' => 'Menu', 'group' => 'Operasional', 'icon' => 'menu', 'owner' => false, 'view' => 'admin.catalog'],
        'pelanggan' => ['label' => 'Pelanggan', 'group' => 'Operasional', 'icon' => 'users', 'owner' => false, 'view' => 'admin.pending'],
        'banner' => ['label' => 'Banner', 'group' => 'Operasional', 'icon' => 'image', 'owner' => false, 'view' => 'admin.pending'],
        'statistik' => ['label' => 'Statistik', 'group' => 'Operasional', 'icon' => 'chart', 'owner' => false, 'view' => 'admin.pending'],
        'promo' => ['label' => 'Diskon & kode promo', 'group' => 'Toko', 'icon' => 'tag', 'owner' => true, 'view' => 'admin.pending'],
        'profil' => ['label' => 'Profil toko', 'group' => 'Toko', 'icon' => 'store', 'owner' => true, 'view' => 'admin.settings'],
        'tampilan' => ['label' => 'Identitas & tampilan', 'group' => 'Toko', 'icon' => 'image', 'owner' => true, 'view' => 'admin.settings'],
        'pengaturan' => ['label' => 'Pengaturan', 'group' => 'Toko', 'icon' => 'settings', 'owner' => true, 'view' => 'admin.settings'],
        'seo' => ['label' => 'SEO toko', 'group' => 'Toko', 'icon' => 'search', 'owner' => true, 'view' => 'admin.settings'],
        'akun-admin' => ['label' => 'Akun admin', 'group' => 'Toko', 'icon' => 'users', 'owner' => true, 'view' => 'admin.staff'],
    ],
];
