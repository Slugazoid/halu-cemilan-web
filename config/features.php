<?php

/*
 * Feature flag. Fitur yang ditunda dimatikan di sini, bukan dihapus dari kode:
 * tabel dan model sudah ada, tetapi rute dan menu tidak muncul sampai flag menyala.
 */
return [

    // Akun pelanggan, riwayat, OTP, alamat berjenjang, menu admin Pelanggan,
    // statistik pelanggan baru/kembali, promo "khusus pelanggan baru",
    // halaman /daftar /masuk /profil. Checkout memakai mode guest selama false.
    'customer_accounts' => (bool) env('FEATURE_CUSTOMER_ACCOUNTS', false),

];
