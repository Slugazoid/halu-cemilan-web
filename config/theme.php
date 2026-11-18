<?php

/*
 * Tema dinamis: warna dan font diambil dari store_settings lalu disuntikkan
 * sebagai CSS variables di layout (lihat StoreSetting::themeCss()).
 * Hanya font di daftar ini yang boleh dipilih admin.
 */
return [

    'default_primary' => '#D9381E',
    'default_accent' => '#F2A900',
    'default_font' => 'Poppins',

    // Font sistem sebagai cadangan agar teks tetap terbaca saat font web belum termuat.
    'fallback_stack' => "ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif",

    // nama font => URL Google Fonts (null = font sistem, tanpa unduhan).
    'fonts' => [
        'Poppins' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
        'Nunito' => 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap',
        'Inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        'System' => null,
    ],

];
