# RuteSales - Sistem Pemantauan Kunjungan Sales Lapangan

**RuteSales** adalah aplikasi berbasis web yang dirancang untuk memantau, mengelola, dan mengevaluasi aktivitas kunjungan tim sales lapangan ke toko dan warung mitra. Aplikasi ini dibangun dengan standar arsitektur **MVC (Model-View-Controller)** menggunakan **Laravel 11.x**, lingkungan pengembangan lokal **Laravel Herd**, basis data **SQLite** yang dihubungkan melalui **TablePlus**, template engine **Blade**, serta framework CSS **Tailwind CSS**.

---

## Daftar Isi
1. [Panduan Instalasi Aplikasi & Lingkungan Lokal](#1-panduan-instalasi-aplikasi--lingkungan-lokal)
2. [Daftar Fitur Utama & Modul Anggota](#2-daftar-fitur-utama--modul-anggota)
3. [Penjelasan Struktur Database (6 Entitas Relasional)](#3-penjelasan-struktur-database-6-entitas-relasional)
4. [Laporan Hasil Skenario Pengujian (TC-01 s.d TC-25)](#4-laporan-hasil-skenario-pengujian-tc-01-sd-tc-25)
5. [Akun Demo & Akses Cepat](#5-akun-demo--akses-cepat)
6. [Pembaruan Hasil Audit (Keamanan, Klarifikasi, Alamat, Laporan)](#6-pembaruan-hasil-audit-keamanan-klarifikasi-alamat-laporan)
7. [Ketergantungan Layanan Eksternal](#7-ketergantungan-layanan-eksternal)

---

## 1. Panduan Instalasi Aplikasi & Lingkungan Lokal

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi **RuteSales** di lingkungan pengembangan lokal:

### A. Persyaratan Sistem & Tooling
- **Framework Utama**: Laravel 12.x.
- **Lingkungan Lokal**: **Laravel Herd** (untuk macOS / Windows).
- **Basis Data**: **SQLite** dihubungkan melalui **TablePlus**.
- **PHP**: Sesuai batas pada `composer.json` (saat ini `^8.5`; dikembangkan di Laravel Herd) dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd` (untuk thumbnail foto PDF).
- **Node.js**: opsional, hanya untuk menjalankan tes JS pencarian alamat (`npm run test:js`, Node 18 atau lebih baru).
- **Composer**: Versi 2.x.

### B. Konfigurasi Lingkungan Lokal via Laravel Herd
Aplikasi ini dioptimalkan untuk berjalan pada **Laravel Herd**:
1. Pastikan **Laravel Herd** sudah terpasang dan berjalan di komputer Anda.
2. Buka terminal pada direktori proyek dan hubungkan ke Herd:
   ```bash
   herd link rutesales
   ```
3. Akses otomatis melalui domain lokal:
   👉 **[http://rutesales.test](http://rutesales.test)** *(atau `php artisan serve` di `http://127.0.0.1:8000`)*.

### C. Langkah Instalasi Langkah-Demi-Langkah

1. **Pasang Dependensi PHP Menggunakan Composer**:
   ```bash
   composer install
   ```

2. **Konfigurasi Berkas Lingkungan (`.env`)**:
   Salin berkas konfigurasi `.env.example` menjadi `.env`:
   ```bash
   Copy-Item .env.example .env
   ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Jalankan Migrasi dan Seeding Basis Data**:
   Perintah ini membuat seluruh skema (termasuk foto, audit log, klarifikasi, dan alamat terstruktur) dan mengisinya dengan data awal realistis. Setelah `git pull` pada instalasi yang sudah berjalan, cukup `php artisan migrate`:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Menjalankan Pengujian Otomatis (Feature & Unit Tests)**:
   ```bash
   php artisan test
   ```

---

## 2. Daftar Fitur Utama & Modul Anggota

Sistem RuteSales dibagi menjadi 4 modul tanggung jawab fungsional:

### A. Modul 1 — Manajemen Data Master (PIC: Rofi)
- CRUD data profil Sales dan Toko Mitra.
- Pengaturan status keaktifan (`active` / `inactive`).
- Proteksi penghapusan: Toko dan Sales yang memiliki riwayat kunjungan tidak dihapus permanen (*hard delete*), melainkan dialihkan statusnya menjadi nonaktif (*inactive*).
- Validasi formulir server-side dan proteksi token CSRF.

### B. Modul 2 — Manajemen Rute Kunjungan (PIC: Nana)
- Pembuatan rute baru dengan memilih sales aktif, tanggal, dan **beberapa toko sasaran berurutan** (`sequence`).
- Pencegahan toko duplikat pada rute yang sama.
- Halaman detail rute dinamis: `/rute/detail/{id}`.
- Pelacakan status rute: `planned`, `in_progress`, `completed`, `cancelled`.

### C. Modul 3 — Kunjungan Lapangan & Pesanan (PIC: Ammar)
- Halaman **Kunjungan Saya** khusus sales login (`/my-visits`).
- Formulir check-in transaksi dengan pencatatan waktu otomatis dari waktu server (*server-time*).
- Hasil kunjungan: `successful` (berhasil), `store_closed` (toko tutup), `failed` (ditolak).
- Validasi wajib alasan kegagalan (`failure_reason`) jika status tutup/gagal.
- Pencatatan pesanan penjualan sederhana (`orders`) khusus kunjungan berhasil (1 pesanan per kunjungan).
- Proteksi hak akses kepemilikan data: Sales tidak dapat melihat atau mengubah tugas sales lain.

### D. Modul 4 — Akses Pengguna, Dashboard & Laporan (PIC: Arif)
- Otentikasi login/logout dengan pengalihan peran (Admin $\rightarrow$ Dashboard, Sales $\rightarrow$ Kunjungan Saya).
- Middleware keamanan berbasis peran (`EnsureAdmin`, `EnsureSales`).
- Layout bersama dan reusable components Blade.
- Dashboard Admin dengan 3 metrik utama yang dihitung langsung dari basis data:
  1. Total Kunjungan Selesai.
  2. Persentase Keberhasilan Kunjungan.
  3. Total Nilai Pesanan.
  *(Aman dari kesalahan pembagian dengan nol saat data kosong).*
- Halaman Laporan Rekapitulasi dengan filter rentang tanggal, sales, toko, hasil kunjungan, dan "perlu ditinjau", serta ekspor PDF (opsional dengan foto) dan CSV.

---

## 3. Penjelasan Struktur Database (6 Entitas Relasional)

Sistem mengimplementasikan 6 tabel relasional terpadu:

```mermaid
erDiagram
    USERS ||--o| SALES : "memiliki profil"
    SALES ||--o{ SALES_ROUTES : "ditugaskan pada"
    SALES_ROUTES ||--|{ ROUTE_VISITS : "memuat titik kunjungan"
    STORES ||--o{ ROUTE_VISITS : "menjadi tujuan"
    ROUTE_VISITS ||--o| ORDERS : "menghasilkan"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role "admin / sales"
        timestamps timestamps
    }

    SALES {
        bigint id PK
        bigint user_id FK "unique, nullable"
        string name
        string phone
        string employee_code UK
        string status "active / inactive"
        timestamps timestamps
    }

    STORES {
        bigint id PK
        string name
        string owner_name
        string phone
        text address
        string area
        decimal latitude
        decimal longitude
        string status "active / inactive"
        text notes
        timestamps timestamps
    }

    SALES_ROUTES {
        bigint id PK
        bigint sales_id FK
        date route_date
        string name
        string status "planned / in_progress / completed / cancelled"
        text notes
        timestamps timestamps
    }

    ROUTE_VISITS {
        bigint id PK
        bigint sales_route_id FK
        bigint store_id FK
        unsigned_integer sequence
        string result "successful / store_closed / failed / null"
        datetime checked_in_at
        text notes
        text failure_reason
        decimal simulated_latitude
        decimal simulated_longitude
        timestamps timestamps
    }

    ORDERS {
        bigint id PK
        bigint route_visit_id FK "unique"
        string order_number UK
        decimal total_amount
        text notes
        timestamps timestamps
    }
```

---

## 4. Laporan Hasil Skenario Pengujian (TC-01 s.d TC-25)

Pengujian otomatis dijalankan menggunakan PHPUnit di Laravel 12. Pada pembaruan terakhir, seluruh **108 tes PHPUnit lulus** (374 assertion) dan 8 tes JS lulus. Tabel berikut adalah 25 skenario dasar dari rencana pengujian; skenario tambahan hasil audit ada di bagian 6:

| Kode Test | Area | Skenario Pengujian | Ekspektasi Hasil | Status |
|:---:|:---|:---|:---|:---:|
| **TC-01** | Auth | Membuka dashboard tanpa login | Dialihkan ke halaman login | **PASS** |
| **TC-02** | Role | Sales membuka master toko | Ditolak & dialihkan ke halaman Kunjungan Saya | **PASS** |
| **TC-03** | Sales | Admin membuat sales valid | Tersimpan di tabel `users` & `sales` | **PASS** |
| **TC-04** | Sales | Pembuatan sales tanpa nama | Validasi server gagal, ditolak | **PASS** |
| **TC-05** | Toko | Admin membuat toko valid (jalan + provinsi, kabupaten/kota, kecamatan, kelurahan) | Data tersimpan di tabel `stores`; alamat dan kode pos tersusun otomatis | **PASS** |
| **TC-06** | Toko | Pembuatan toko tanpa alamat lengkap | Validasi server gagal pada kunci `address`, ditolak | **PASS** |
| **TC-07** | Rute | Admin membuat rute sales aktif & multi-toko | Rute & item kunjungan terurut tersimpan | **PASS** |
| **TC-08** | Rute | Menugaskan sales nonaktif ke rute | Validasi gagal, penugasan ditolak | **PASS** |
| **TC-09** | Rute | Menambahkan toko nonaktif ke rute | Validasi gagal, penambahan ditolak | **PASS** |
| **TC-10** | Rute | Menambahkan toko duplikat pada rute | Validasi gagal, toko ganda ditolak | **PASS** |
| **TC-11** | Detail Rute | Membuka rute dengan ID valid (`/rute/detail/{id}`) | Merender urutan toko & informasi rute | **PASS** |
| **TC-12** | Detail Rute | Membuka rute dengan ID tidak terdaftar | Mengembalikan HTTP 404 Not Found | **PASS** |
| **TC-13** | Kunjungan | Check-in hasil `successful` | Tersimpan dengan waktu server aktual | **PASS** |
| **TC-14** | Kunjungan | Hasil `failed` tanpa alasan | Validasi gagal, wajib mengisi alasan | **PASS** |
| **TC-15** | Kunjungan | Hasil `store_closed` tanpa alasan | Validasi gagal, wajib mengisi alasan | **PASS** |
| **TC-16** | Otorisasi | Sales mencoba membuka rute sales lain | Akses ditolak dengan HTTP 403 Forbidden | **PASS** |
| **TC-17** | Pesanan | Input pesanan pada kunjungan `successful` | Catatan pesanan tersimpan di tabel `orders` | **PASS** |
| **TC-18** | Pesanan | Input pesanan pada kunjungan `failed` | Ditolak validasi | **PASS** |
| **TC-19** | Pesanan | Nilai pesanan negatif atau non-numerik | Validasi gagal, tidak tersimpan | **PASS** |
| **TC-20** | Dashboard | Perhitungan metrik saat data kunjungan ada | Angka metrik akurat sesuai data database | **PASS** |
| **TC-21** | Dashboard | Perhitungan metrik saat data kosong | Nilai 0, bebas galat pembagian nol | **PASS** |
| **TC-22** | Keamanan | Request formulir web tanpa token CSRF | Ditolak sistem CSRF Laravel | **PASS** |
| **TC-23** | Kinerja | Daftar data panjang melebihi batas | Fitur pagination berfungsi normal | **PASS** |
| **TC-24** | Antarmuka | Responsivitas tampilan pada layar mobile | Layout dan menu mobile dapat digunakan | **PASS** |
| **TC-25** | Setup | Fresh database setup & seeding | Seluruh migrasi dan data demo berjalan lancar | **PASS** |

Jalankan test kapan saja melalui:
```bash
php artisan test
```

---

## 5. Akun Demo & Akses Cepat

Data awal yang siap digunakan untuk evaluasi:

| Peran | Nama | Email | Password | Keterangan |
|:---|:---|:---|:---|:---|
| **Administrator** | Administrator Utama | `admin@rutesales.com` | `password` | Mengelola rute, toko, sales, & laporan |
| **Sales Lapangan** | Budi Santoso | `budi@rutesales.com` | `password` | Akses Kunjungan Saya (SLS-101) |
| **Sales Lapangan** | Siti Rahma | `siti@rutesales.com` | `password` | Akses Kunjungan Saya (SLS-102) |
| **Sales Lapangan** | Ahmad Fauzi | `fauzi@rutesales.com` | `password` | Akses Kunjungan Saya (SLS-103) |

---

## 6. Pembaruan Hasil Audit (Keamanan, Klarifikasi, Alamat, Laporan)

Perubahan berikut ditambahkan setelah audit kode. Setiap poin memiliki tes otomatis.

### A. Keamanan dan integritas data
- Akun nonaktif tidak dapat login, dan sesi aktif akun yang dinonaktifkan dihentikan (`EnsureUserIsActive`). Login dibatasi 5 percobaan gagal per menit.
- Admin tidak dapat menonaktifkan, menurunkan peran, atau menghapus akunnya sendiri.
- Hak akses kunjungan dan foto bukti dipusatkan pada **Policy** (`RouteVisitPolicy`, `VisitPhotoPolicy`), bukan pemeriksaan tersebar di controller. Foto disimpan privat dan hanya dapat dibuka admin atau sales pemilik.
- Check-in hanya pada tanggal rute, foto bukti wajib (1 sampai 3), GPS dibandingkan dengan koordinat toko (radius 200 m, batas akurasi 500 m; lihat `config/rutesales.php`). Lokasi jauh tidak memblokir check-in, tetapi ditandai agar ditinjau.
- Melompati urutan toko wajib disertai alasan. Seluruh pembuatan dan perubahan check-in tercatat di tabel `visit_audit_logs`.

### B. Admin hanya memantau, klarifikasi lewat sales
- Admin **tidak dapat** mencatat atau mengubah hasil check-in (HTTP 403). Ini mencegah hasil lapangan diubah setelah sales selesai berkunjung.
- Jika hasil perlu dipertanyakan, admin menekan **Minta Klarifikasi** pada detail rute. Sales melihat banner permintaan, wajib mengisi jawaban, dan boleh menjawab walau tanggal rute sudah lewat (hanya selama klarifikasi terbuka).
- Permintaan dan jawaban tercatat di audit log, tampil di daftar kunjungan ("Perlu klarifikasi"), serta di laporan web dan PDF.
- Rute yang sudah memiliki check-in tidak dapat dihapus (status tidak diubah). Daftar toko pada rute juga terkunci setelah check-in pertama.

### C. Alamat toko terstruktur
- Form toko memakai dropdown berjenjang: provinsi, kabupaten/kota, kecamatan, kelurahan/desa, lalu kolom jalan. Alamat lengkap dan kode pos disusun server dan hierarkinya divalidasi (kombinasi tidak konsisten ditolak).
- Nama wilayah yang ikut tertempel di kolom jalan dibuang otomatis agar alamat tidak ganda.
- Peta langsung menuju pusat wilayah yang dipilih. Tombol **Cari Lokasi dari Alamat** mencari titik jalan lewat Nominatim secara bertahap (jalan + kelurahan, lalu kecamatan, lalu kabupaten/kota), dibatasi pada area terpilih dan jarak maksimum 8 km dari pusat wilayah. Pin dapat digeser manual.
- Data wilayah berupa berkas statis di `public/data/wilayah/` (sumber dan lisensi MIT: lihat `SUMBER.md` di folder itu). Toko lama beralamat teks tetap dapat diedit; alamat lamanya dipertahankan sampai diisi ulang lewat dropdown.
- Kolom baru pada `stores`: `street`, `province_code`, `regency_code`, `district_code`, `village_code`, `postal_code`. Kolom baru pada `route_visits`: `skip_reason`, `gps_accuracy_m`, `gps_distance_m`, `clarification_*`. Tabel baru: `visit_photos`, `visit_audit_logs`.

### D. Laporan
- Filter: rentang tanggal, sales, toko, hasil kunjungan (berhasil, toko tutup, gagal, belum check-in), dan "perlu ditinjau". Ringkasan dan omzet mengikuti filter yang sama.
- Ekspor **PDF** (maksimal 500 baris; foto hingga 100 thumbnail) dan **CSV** (pemisah titik koma dan BOM UTF-8 agar rapi di Excel berlokal Indonesia; sel yang diawali `=`, `+`, `-`, atau `@` dinetralkan dari injeksi rumus).

### E. Tes tambahan
Berkas `tests/Feature/`: `AuditHardeningTest`, `VisitControlsTest`, `RouteEditDashboardTest`, `ClarificationTest`, `StoreAddressTest`, `ReportTest`. Tes JS pencarian alamat: `npm run test:js`.

---

## 7. Ketergantungan Layanan Eksternal

Aplikasi memerlukan internet untuk beberapa fitur. Semuanya layanan gratis dengan batas pemakaian wajar, sehingga perlu dipertimbangkan sebelum dipakai di skala besar:

| Layanan | Dipakai untuk | Akibat jika tidak tersedia |
|:---|:---|:---|
| OpenStreetMap Nominatim | Pencarian titik jalan dari alamat (maksimal 1 permintaan per detik, dipatuhi aplikasi) | Pin tetap di pusat kelurahan dan tampil pesan; admin menggeser pin atau klik peta |
| Tile OpenStreetMap | Gambar peta | Peta kosong; koordinat tetap dapat diisi manual |
| OSRM | Garis rute pada peta rute | Peta tetap tampil tanpa garis rute |
| CDN (Tailwind, Alpine.js, Leaflet) | Tampilan dan interaksi | Halaman tidak tertata atau interaksi tidak berjalan; untuk produksi sebaiknya di-build dan dilayani sendiri |

Catatan: koordinat tetap dapat diketik manual dan data wilayah dilayani dari server sendiri (tanpa layanan luar). Hasil pencarian Nominatim bergantung pada kelengkapan data OpenStreetMap, sehingga pin selalu perlu diperiksa admin. Kualitas GPS pada ponsel lapangan belum diuji otomatis; uji satu check-in dari ponsel di lokasi toko sebelum dipakai nyata.
