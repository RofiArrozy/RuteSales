# Spesifikasi Teknis — RuteSales

## 1. Stack dan batasan
- Laravel 11 dan PHP versi yang kompatibel.
- MVC: Routes, Controllers, Models, Views.
- Blade wajib untuk rendering halaman.
- Pilih satu: Tailwind CSS **atau** Bootstrap.
- Pilih satu database: SQLite **atau** MySQL.
- JavaScript hanya seperlunya; tidak perlu framework frontend terpisah untuk MVP.
- Git/GitHub untuk kolaborasi.

## 2. Prinsip arsitektur
- `routes/web.php` mendefinisikan route dengan nama yang konsisten.
- Controller mengatur alur request; validasi menggunakan mekanisme Laravel.
- Model menggunakan Eloquent dan relasi yang eksplisit.
- Blade menggunakan satu layout utama dan komponen reusable.
- Gunakan middleware/policy/gate atau pemeriksaan otorisasi Laravel yang sesuai.
- Query dashboard harus berasal dari database.
- Gunakan eager loading ketika menampilkan relasi dan pagination untuk daftar panjang.
- Gunakan migration untuk perubahan skema; jangan mengubah skema database manual tanpa mencerminkan perubahan di migration.

## 3. Konvensi nama
- `User`
- `Sales`
- `Store`
- `SalesRoute` (hindari kebingungan dengan facade routing `Route`)
- `RouteVisit`
- `Order`

Controller yang disarankan:
- `DashboardController`
- `SalesController`
- `StoreController`
- `SalesRouteController`
- `RouteVisitController`
- `OrderController`
- `ReportController` (jika laporan dipisahkan)

Route name yang disarankan:
- `login`, `logout`, `dashboard`
- `sales.index`, `sales.create`, `sales.store`, `sales.edit`, `sales.update`
- `stores.index`, `stores.create`, `stores.store`, `stores.edit`, `stores.update`
- `routes.index`, `routes.create`, `routes.store`, `routes.show`, `routes.edit`, `routes.update`
- `route-visits.index`, `route-visits.show`, `route-visits.check-in`
- `orders.store`, `orders.show`
- `reports.index`

Nama final harus disepakati dan tidak diganti sepihak.

## 4. Struktur Blade yang disarankan
```text
resources/views/
  layouts/app.blade.php
  components/
    alert.blade.php
    form-error.blade.php
    status-badge.blade.php
    page-header.blade.php
  auth/login.blade.php
  dashboard/index.blade.php
  sales/index.blade.php
  sales/create.blade.php
  sales/edit.blade.php
  stores/index.blade.php
  stores/create.blade.php
  stores/edit.blade.php
  routes/index.blade.php
  routes/create.blade.php
  routes/show.blade.php
  routes/edit.blade.php
  route-visits/index.blade.php
  route-visits/show.blade.php
  orders/create.blade.php
  reports/index.blade.php
```

## 5. Validasi dan keamanan
- Password disimpan dengan hash Laravel, bukan teks biasa.
- Form perubahan data memakai `@csrf`; form update/delete memakai method spoofing yang sesuai.
- Validasi dilakukan di server, tidak hanya di browser.
- Gunakan `$fillable` eksplisit atau strategi `$guarded` yang aman dan dipahami.
- Otorisasi harus dilakukan di server, termasuk saat pengguna mengubah ID pada URL/form.
- Jangan mempercayai `sales_id`, `user_id`, waktu check-in, atau role yang dikirim browser tanpa pemeriksaan.
- Check-in menggunakan waktu server.
- Tampilkan pesan error tanpa membocorkan stack trace pada penggunaan normal.
- Jangan commit `.env`, credential, token, atau file rahasia.

## 6. Database dan relasi
Lihat `ERD.md`. Seluruh foreign key, unique constraint, dan aturan penghapusan harus tercermin dalam migration. Untuk sales/toko yang memiliki riwayat, gunakan status nonaktif agar data historis tidak rusak.

## 7. Modul praktikum yang harus terlihat dalam implementasi
- Instalasi/environment dan `.env`.
- Routes.
- Layout bersama.
- Blade dan reusable components.
- Tailwind CSS atau Bootstrap.
- Koneksi database.
- Migration.
- Factory.
- Relasi database.
- Eager loading/N+1 dan pagination.
- Seeder dan form Blade dengan CSRF.
- Request validation dan `$fillable`/`$guarded`.

## 8. Pilihan lingkungan
SQLite mudah untuk setup lokal; MySQL dapat dipilih jika kelompok membutuhkan pengelolaan database terpisah. Pilih satu untuk konfigurasi utama, dokumentasikan cara menjalankan, dan pastikan semua anggota menguji dengan setup yang sama.
