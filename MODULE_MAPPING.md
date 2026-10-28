# Pemetaan RuteSales terhadap Modul Laravel 11 Fundamental

**Sumber acuan:** *Modul Laravel Edit (1).pdf* yang diberikan kelompok. Tabel ini memetakan materi praktikum ke kemungkinan penerapan pada RuteSales; bukan klaim bahwa modul secara khusus meminta fitur bisnis RuteSales.

| Acara | Materi modul | Penerapan pada RuteSales | Bukti yang dapat diperiksa |
|---|---|---|---|
| I | Instalasi/environment | Setup Laravel 11, `.env`, server lokal | README dan aplikasi berjalan |
| II | Routes | Route login, dashboard, master data, rute, kunjungan, pesanan | `routes/web.php` |
| III | Three Page Layout | Layout bersama untuk halaman internal | Layout dipakai di beberapa halaman |
| IV | Blade | Blade views dan reusable components | Layout, badge, alert, form error |
| V | Tailwind CSS | UI responsif; Bootstrap juga boleh jika disetujui dosen | Tampilan konsisten |
| VI | Koneksi database/TablePlus | Konfigurasi SQLite atau MySQL dan pemeriksaan data | Koneksi berhasil |
| VII | Database | Migration dan struktur tabel | `php artisan migrate` |
| VIII | Factory | Membuat data dummy | Factory bisa menghasilkan data |
| IX | Database Relation | Relasi sales, rute, toko, kunjungan, pesanan | Relasi Eloquent |
| X | N+1 problem | Eager loading dan pagination | Daftar relasi efisien |
| XI | Seeder and Form | Seeder, form Blade, CSRF, pagination | Data awal dan form berjalan |
| XII | Request Validation | Validasi request dan `$fillable`/`$guarded` | Input salah ditolak |

## Prinsip implementasi
1. Pertahankan Laravel MVC: route, controller, model, view.
2. Gunakan Blade, layout, dan reusable components.
3. Pilih Tailwind CSS atau Bootstrap secara konsisten.
4. Gunakan migration, factory, seeder, relasi Eloquent, validasi, dan CSRF.
5. Gunakan eager loading dan pagination sesuai kebutuhan.
6. Jangan menyalin konfigurasi versi lama tanpa memeriksa proyek Laravel 11 yang benar-benar digunakan.
7. Ketentuan dosen lebih tinggi daripada usulan desain di dokumen ini.
