# Software Design Specification (SDS) — RuteSales

## A. Lingkungan & Arsitektur Teknologi
Dokumen ini menetapkan standar arsitektur dan teknologi yang digunakan dalam proyek RuteSales:
- **Framework Utama**: Laravel 11.x (PHP 8.2+) dengan pola arsitektur **MVC (Model-View-Controller)** dan perutean terpusat di `routes/web.php`.
- **Lingkungan Pengembangan Lokal**: **Laravel Herd** (Windows / macOS) dengan web server lokal terintegrasi.
- **Basis Data**: **SQLite** diakses dan dikelola melalui TablePlus / DB Browser for SQLite. Konfigurasi tersimpan pada berkas `.env`.
- **Antarmuka Pengguna (Frontend)**: Template engine **Blade** didukung framework CSS **Tailwind CSS** untuk antarmuka yang konsisten, modern, dan responsif (desktop & mobile). Menggunakan komponen Blade reusable (`layouts/app.blade.php`, alerts, badges, headers).
- **Keamanan & Otorisasi**: Proteksi CSRF (`@csrf`), hashing password dengan bcrypt, validasi *server-side* dengan Form Request, dan pemisahan hak akses berbasis peran (*Role-Based Access Control*) antara Admin dan Sales.

---

## B. Desain Basis Data (6 Entitas Relasional Terintegrasi)
Sistem menggunakan skema relasional terstruktur yang dikonfigurasi melalui Migration, Eloquent Model, Factory, dan Database Seeder:

1. **`users`** (Tabel Akun Autentikasi)
   - Kolom: `id`, `name`, `email` (UK), `password`, `role` (`admin`/`sales`), `timestamps`.
2. **`sales`** (Tabel Profil Tenaga Sales)
   - Kolom: `id`, `user_id` (FK to `users`, unique), `name`, `phone`, `status` (`active`/`inactive`), `timestamps`.
3. **`stores`** (Tabel Toko/Warung Mitra)
   - Kolom: `id`, `name`, `owner_name`, `phone`, `address`, `area`, `latitude`, `longitude`, `status` (`active`/`inactive`), `notes`, `timestamps`.
4. **`sales_routes`** (Tabel Rute Penugasan Sales)
   - Kolom: `id`, `sales_id` (FK to `sales`), `route_date`, `name`, `status` (`planned`/`in_progress`/`completed`/`cancelled`), `notes`, `timestamps`.
5. **`route_visits`** (Tabel Kunjungan Multi-Toko dalam Rute)
   - Kolom: `id`, `sales_route_id` (FK to `sales_routes`), `store_id` (FK to `stores`), `sequence` (urutan kunjungan), `result` (`successful`/`store_closed`/`failed`), `checked_in_at`, `notes`, `failure_reason`, `timestamps`.
   - Constraints: Unique `(sales_route_id, sequence)` dan Unique `(sales_route_id, store_id)`.
6. **`orders`** (Tabel Catatan Pesanan Sederhana)
   - Kolom: `id`, `route_visit_id` (FK to `route_visits`, unique), `order_number` (UK), `total_amount`, `notes`, `timestamps`.

---

## C. Pembagian Modul & Tanggung Jawab Anggota
1. **Modul 1 — Manajemen Data Master (PIC: Rofi)**
   - CRUD data sales dan toko, status aktif/nonaktif, pencegahan hapus data berelasi, validasi input.
2. **Modul 2 — Manajemen Rute Kunjungan (PIC: Nana)**
   - Penyusunan rute sales harian, pemilihan multi-toko aktif, pengaturan urutan kunjungan (`sequence`), pencegahan duplikasi toko dalam rute, dan halaman detail rute dinamis (`/rute/detail/{id}`).
3. **Modul 3 — Kunjungan Lapangan & Pesanan (PIC: Ammar)**
   - Halaman "Kunjungan Saya" untuk sales, check-in dengan pencatatan waktu server, validasi alasan gagal/tutup, pembuatan pesanan untuk kunjungan berhasil.
4. **Modul 4 — Autentikasi, Dashboard & Laporan (PIC: Arif)**
   - Sistem login/logout, middleware otorisasi, layout Blade bersama, dashboard metrik nyata dari database, dan laporan rekapitulasi.

---

## D. Struktur Dokumentasi & Pengujian
Sistem dilengkapi dengan:
1. Panduan instalasi dan penggunaan pada `README.md`.
2. Spesifikasi kebutuhan fungsional pada `SRS.md`.
3. Minimal 25 skenario pengujian komprehensif (`TC-01` s.d. `TC-25`) yang dapat diuji otomatis melalui `php artisan test`.
