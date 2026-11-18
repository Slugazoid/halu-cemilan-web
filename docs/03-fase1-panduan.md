# Fase 1 — Fondasi: panduan, pembagian layer, dan checklist

Status: kode selesai dan lolos 74 tes otomatis di sandbox (PHP 8.3, SQLite).
Yang belum terbukti: jalan di MySQL Laragon milik Albert (langkah 5 di bawah).

## Pembagian layer

| Layer | Isi | Lokasi |
|---|---|---|
| **Database** | 27 tabel (7 file migration per domain), seeder dari mock | `database/migrations/`, `database/seeders/`, `mock/` |
| **Backend** | Enum, 26 model + relasi, morph map, class pendukung (nomor HP, Rupiah, warna, slug, cache publik), guard admin & customer, feature flag, locale/zona waktu, controller beranda | `app/`, `config/`, `bootstrap/app.php`, `routes/`, `lang/` |
| **Frontend** | Tema CSS variables dari `store_settings` di layout storefront, Tailwind; storefront + admin Livewire dari Naufal | `resources/views/`, `resources/css/`, `resources/js/`, `vite.config.js` |
| **Tes** | 74 tes unit + feature (termasuk tes admin Naufal) | `tests/` |

## Langkah menjalankan (Albert, Windows + Laragon/Cmder)

Patch ini sudah **menggabungkan** Fase 1 dengan branch `admin-storefront/scaffolding` (Naufal).
Jadi basisnya branch Naufal, bukan `main`.

1. `git fetch origin`
2. `git checkout -b feat/fase1-integrasi origin/admin-storefront/scaffolding`
3. Taruh `fase1-integrasi.patch` di root project, lalu `git apply --index fase1-integrasi.patch`
   (jika gagal, kirim pesan galatnya ke Claude), lalu hapus file patch-nya.
4. Update `.env` lokal (tidak ikut git). Pastikan baris ini ada:
   ```
   APP_TIMEZONE=Asia/Jakarta
   APP_LOCALE=id
   APP_FAKER_LOCALE=id_ID
   DB_CONNECTION=mysql
   DB_DATABASE=halu_cemilan
   FEATURE_CUSTOMER_ACCOUNTS=false
   SEED_OWNER_PASSWORD=GantiSandi123
   ```
5. `composer install` (lock sudah diperbaiki agar jalan di PHP 8.3, lihat bawah), lalu `npm install` dan `npm run build`.
6. `php artisan migrate:fresh --seed` (menghapus semua tabel lalu mengisi ulang; aman karena belum ada data asli).
7. `php artisan test` → harus 74 lulus.
8. `composer run dev`, lalu buka:
   - http://localhost:8000 → beranda toko (data seeder)
   - http://localhost:8000/menu → daftar menu per kategori
   - http://localhost:8000/admin/masuk → login admin: HP `6281200000001`, sandi = `SEED_OWNER_PASSWORD`, lalu diminta ganti sandi.
9. `vendor\bin\pint`, commit, push `feat/fase1-integrasi`, buka Pull Request ke `main`.
10. Minta Naufal me-review PR (bagian admin/storefront miliknya ikut di PR ini).

## Hasil penggabungan dengan branch Naufal

Dipertahankan dari Naufal: login admin (HP/email, rate limit, ganti sandi sementara), Livewire (setup, menu, staf, pengaturan toko),
middleware, halaman storefront (`/`, `/tentang`, `/menu`, `/menu/{slug}`, legal, sitemap), `robots.txt`, `owner_slot` (tepat satu Pemilik, BR-33), tes admin.

Disesuaikan agar cocok:
- **Migration**: dua migration Naufal (admins, store_settings+katalog) digantikan migration Fase 1 yang lebih lengkap. Kolom Naufal (`owner_slot`, `temporary_password_expires_at`, baris kosong `store_settings` id=1) dibawa masuk. Kolom inti `store_settings` dibuat nullable karena wizard setup yang mengisinya.
- **`opening_hours`** tetap teks bebas (punya Naufal), bukan JSON per hari seperti PRD.
- **Tabel `admin_password_resets`** dihapus; catatan reset sandi staf masuk `activity_logs` (BR-35, tetap 27 tabel).
- **Model `User` dihapus** (tidak dipakai). Tes Naufal yang memakai `User` diganti `Customer` + factory baru.
- **Tema toko**: layout storefront Naufal sekarang memakai `StoreSetting::themeCss()` (warna + font + kontras otomatis).
- **Beranda sementara Fase 1** (HomeController dkk.) dibuang; storefront Naufal dipakai.
- **Admin**: `role`, `is_active`, `owner_slot` tetap tidak boleh mass-assign (gaya `#[Fillable]` Naufal).
- **Alpine.js**: TIDAK dipasang lewat npm. Livewire 4 sudah membawa Alpine sendiri; memasang dua kali bikin bentrok. Untuk halaman publik non-Livewire (keranjang) cara memuatnya diputuskan di Fase 4.
- **`laravel/boost` dibuang dari composer.json/lock**: lock Naufal tidak bisa dipasang di PHP 8.3 karena paket dev `symfony/yaml` v8 minta PHP ≥ 8.4.1. Kalau Naufal ingin tetap memakai Boost, pakai PHP 8.4 secara lokal tanpa meng-commit perubahan composer.

## Istilah singkat

- **Migration**: skrip PHP yang membuat/mengubah tabel database, bisa diulang dan diversikan di git.
- **Model**: class PHP yang mewakili satu tabel (mis. `MenuItem` = tabel `menu_items`) beserta relasinya.
- **Seeder**: skrip pengisi data awal.
- **Route**: daftar alamat URL → kode yang menanganinya (`routes/web.php`).
- **Blade**: bahasa template HTML Laravel (`*.blade.php`).
- **Guard**: "pintu login" terpisah; di sini `admin` dan `customer`.
- **Feature flag**: saklar di config untuk menyalakan/mematikan fitur tanpa menghapus kode.

## Keputusan teknis yang perlu diketahui tim

- Tabel `users` dan `password_reset_tokens` bawaan Laravel dibuang; model `User` dihapus. Login memakai tabel `admins` dan `customers`.
- `orders.id` = BIGINT auto-increment (salah ketik PRD dirapikan). `orders.customer_id` boleh NULL (checkout guest); batas kode promo per pelanggan memakai `customer_phone` (628…). Ditambah index `(promo_code_id, customer_phone)`.
- Kolom `remember_token` ditambahkan di `admins` dan `customers` (tidak ada di PRD) supaya fitur "ingat saya" bisa dipakai nanti.
- Tabel wilayah (provinces…villages) dibuat tapi kosong; data Kemendagri diisi saat fitur akun dinyalakan.
- Cache publik memakai "versi" (`PublicCache`) karena driver cache = database tidak punya tag. Model publik otomatis membersihkan cache saat disimpan/dihapus.
- Font toko dimuat dari Google Fonts sesuai pengaturan toko (daftar boleh-pilih di `config/theme.php`); variabel CSS dinamis memakai prefiks `--store-*`.
- Belum dikerjakan (sesuai rencana): kode pesanan HC-0001 (BR-22) dan harga/diskon → Fase 4.
- `AGENTS.md`/`CLAUDE.md` bawaan Laravel meminta memasang Laravel Boost; tidak dipasang karena tidak diminta.

## Checklist Fase 1

### Database
- [x] 27 tabel aplikasi dibuat (7 migration per domain) + tabel bawaan sessions/cache/jobs
- [x] Salah ketik PRD dirapikan (`orders.id` BIGINT)
- [x] Seeder dari `mock/halu-cemilan.json`: toko, 1 Pemilik, 4 kategori, 6 menu, 3 grup varian, 8 opsi, 2 FAQ, 1 testimoni
- [x] Seeder aman dijalankan berulang (tanpa data ganda)
- [x] `migrate:fresh --seed` terbukti jalan di SQLite
- [ ] `migrate:fresh --seed` terbukti jalan di **MySQL** (Albert, langkah 5)
- [ ] Data wilayah Kemendagri (ditunda sampai fitur akun)

### Backend
- [x] Enum PHP (peran admin, tipe diskon, tipe link banner, kanal OTP, tipe event, sumber trafik)
- [x] 26 model + relasi + soft delete (menu, kategori) + morph map alias pendek
- [x] Normalisasi nomor HP ke 628… (BR-12)
- [x] Format Rupiah + pembulatan ke bawah kelipatan Rp100 (BR-36)
- [x] Pembuat slug unik maks. 60 karakter (BR-49)
- [x] Mekanisme cache publik + pembersihan otomatis lewat model event
- [x] Dua guard (admin aktif, customer terdaftar) + tes login/nonaktif/isolasi sesi
- [x] Feature flag `customer_accounts=false` + rute pelanggan hanya dimuat bila menyala
- [x] Bahasa Indonesia, zona waktu Asia/Jakarta, pesan validasi/auth Indonesia
- [x] Login, ganti sandi, dan hak akses admin (dikerjakan Naufal, sudah digabung)

### Frontend
- [x] Layout Blade (`<x-layouts.app>`) dengan title, description, canonical, noindex opsional
- [x] Tema dinamis: warna & font dari `store_settings` → CSS variables, teks otomatis kontras AA
- [x] Storefront dasar (Naufal): beranda, tentang, katalog, detail menu, legal, sitemap; "Segera hadir" bila belum terbit
- [x] Tailwind 4 terpasang, `npm run build` sukses (Alpine datang dari Livewire)
- [ ] Cek tampilan di HP sungguhan (Albert)
- [ ] Beranda lengkap, katalog, detail menu (Fase 3)

### Tes
- [x] 74 tes lulus (unit: nomor HP, Rupiah, warna; feature: skema, seeder, relasi, auth, flag, cache, slug, beranda, locale)
- [ ] `php artisan test` hijau di laptop Albert
