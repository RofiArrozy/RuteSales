# TODO Checklist — RuteSales

Gunakan checklist ini sebagai papan kerja kelompok. Seluruh penyesuaian telah diuji dan diverifikasi.

## Fase 0 — Brainstorming dan keputusan
- [x] Semua anggota membaca README, PRD, SRS, ERD.
- [x] Konfirmasi ruang lingkup MVP (total nominal saja untuk pesanan).
- [x] Putuskan Tailwind CSS (disetujui).
- [x] Putuskan SQLite (disetujui).
- [x] Putuskan apakah sales wajib punya akun (ya, memiliki akun login).
- [x] Putuskan struktur pesanan: total nominal saja (`orders`).
- [x] Sepakati daftar status rute (`planned`, `in_progress`, `completed`, `cancelled`) dan hasil kunjungan (`successful`, `store_closed`, `failed`).
- [x] Setujui ERD dan constraints 6 entitas.
- [x] Tentukan nama branch dan aturan Pull Request.

## Fase 1 — Fondasi
- [x] Buat repository dan struktur proyek.
- [x] Siapkan Laravel 11 (PHP 8.2+).
- [x] Konfigurasi database SQLite (`database.sqlite`).
- [x] Buat migration 6 tabel relasional (`users`, `sales`, `stores`, `sales_routes`, `route_visits`, `orders`).
- [x] Jalankan migration dari kondisi database kosong (`php artisan migrate:fresh --seed`).
- [x] Buat model dan relasi Eloquent (`User`, `Sales`, `Store`, `SalesRoute`, `RouteVisit`, `Order`).
- [x] Buat factory dan seeder data demo realistis.
- [x] Siapkan layout Blade utama (`components/layout.blade.php`).
- [x] Siapkan reusable components (`badge`, `alert`, `stat-card`).
- [x] Siapkan autentikasi dan middleware role (`EnsureAdmin`, `EnsureSales`).

## Fase 2 — Fitur
### Data Master (Rofi)
- [x] CRUD sales terhubung ke profil `sales`.
- [x] CRUD toko (`stores`).
- [x] Status aktif/nonaktif (`active`/`inactive`).
- [x] Validasi server-side dan pesan error informatif.
- [x] Search dan pagination pada data master.
- [x] Proteksi hapus: nonaktifkan jika memiliki riwayat.

### Rute (Nana)
- [x] Daftar rute (`rute.index`).
- [x] Buat/edit rute (`rute.create`, `rute.edit`).
- [x] Pilih sales aktif (validasi status aktif).
- [x] Tambah toko aktif multi-stop (validasi status aktif).
- [x] Atur urutan kunjungan (`sequence`).
- [x] Cegah toko ganda dalam rute yang sama.
- [x] Detail rute dinamis (`/rute/detail/{id}`).

### Kunjungan dan Pesanan (Ammar)
- [x] Sales melihat kunjungan miliknya di halaman Kunjungan Saya (`/my-visits`).
- [x] Check-in mencatat waktu server otomatis (`now()`).
- [x] Pilihan hasil kunjungan: `successful`, `store_closed`, `failed`.
- [x] Alasan wajib untuk `failed`/`store_closed`.
- [x] Pesanan hanya untuk kunjungan berhasil (`successful`).
- [x] Validasi total nominal (numerik, non-negatif).
- [x] Cegah akses ke kunjungan sales lain (403 Forbidden).

### Dashboard dan Laporan (Arif)
- [x] Total kunjungan dari database.
- [x] Jumlah kunjungan berhasil/gagal/toko tutup.
- [x] Persentase keberhasilan (dengan proteksi pembagian nol).
- [x] Total nilai pesanan akumulatif.
- [x] Penanganan data kosong (*empty state*).
- [x] Halaman laporan dengan filter rentang tanggal (`/reports`).

## Fase 3 — Uji dan rilis
- [x] Minimal 10 skenario test dijalankan (seluruh 28 test cases lolos 100% PASS).
- [x] Uji role admin dan sales.
- [x] Uji input tidak valid.
- [x] Uji akses ID milik sales lain (TC-16).
- [x] Uji data kosong (TC-21).
- [x] Uji responsive di desktop dan ponsel.
- [x] Periksa query relasi dan eager loading (bebas N+1).
- [x] Pastikan tidak ada secret di repository.
- [x] README mencakup setup, migrate, seed, run, test.
- [x] Fresh setup diuji dan berjalan lancar.
- [x] Siapkan akun demo aman untuk presentasi (Admin, Budi, Siti, Fauzi).
- [x] Dokumen penyesuaian lengkap tersinkronisasi.
