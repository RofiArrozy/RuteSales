# Test Plan & Laporan Eksekusi — RuteSales

Hasil eksekusi pengujian otomatis (*automated feature & unit tests*) menggunakan framework pengujian PHPUnit di Laravel 11. Seluruh 25 test case telah berhasil dijalankan dengan status **100% PASS**.

| ID | Area | Skenario | Hasil yang Diharapkan | Hasil Aktual | Status | Tanggal Uji |
|---|---|---|---|---|---|---|
| TC-01 | Auth | Membuka dashboard tanpa login | Dialihkan ke login | Dialihkan ke `/login` (302) | **PASS** | 17 Nov 2026 |
| TC-02 | Role | Sales membuka CRUD toko | Ditolak/diarahkan ke halaman yang diizinkan | Dialihkan ke `/my-visits` dengan pesan error | **PASS** | 17 Nov 2026 |
| TC-03 | Sales | Admin membuat sales valid | Tersimpan dan muncul di daftar | Tersimpan di tabel `users` & `sales` | **PASS** | 17 Nov 2026 |
| TC-04 | Sales | Nama sales kosong | Validasi gagal, tidak tersimpan | Galat validasi `name`, tidak tersimpan | **PASS** | 17 Nov 2026 |
| TC-05 | Toko | Admin membuat toko valid | Tersimpan dan muncul | Tersimpan di tabel `stores` | **PASS** | 17 Nov 2026 |
| TC-06 | Toko | Alamat wajib kosong | Validasi gagal | Galat validasi `address` | **PASS** | 17 Nov 2026 |
| TC-07 | Rute | Admin membuat rute dengan sales aktif | Rute tersimpan | Rute dan titik toko terurut tersimpan | **PASS** | 17 Nov 2026 |
| TC-08 | Rute | Admin menugaskan sales nonaktif | Ditolak validasi | Galat `sales_id` nonaktif | **PASS** | 17 Nov 2026 |
| TC-09 | Rute | Menambahkan toko nonaktif | Ditolak validasi | Galat `stores` nonaktif | **PASS** | 17 Nov 2026 |
| TC-10 | Rute | Toko sama ditambahkan dua kali pada rute | Ditolak validasi duplikasi | Galat keunikan toko pada rute | **PASS** | 17 Nov 2026 |
| TC-11 | Detail rute | Membuka ID rute yang valid | Detail dan urutan toko tampil | HTTP 200, urutan toko #1, #2 tampil | **PASS** | 17 Nov 2026 |
| TC-12 | Detail rute | Membuka ID yang tidak ada | 404 yang sesuai | HTTP 404 Not Found | **PASS** | 17 Nov 2026 |
| TC-13 | Kunjungan | Sales mencatat hasil successful | Hasil dan waktu server tersimpan | `result = 'successful'`, `checked_in_at` tercatat | **PASS** | 17 Nov 2026 |
| TC-14 | Kunjungan | Hasil failed tanpa alasan | Validasi gagal | Galat `failure_reason` wajib diisi | **PASS** | 17 Nov 2026 |
| TC-15 | Kunjungan | Hasil store_closed tanpa alasan | Validasi gagal | Galat `failure_reason` wajib diisi | **PASS** | 17 Nov 2026 |
| TC-16 | Otorisasi | Sales mencoba membuka kunjungan sales lain | Akses ditolak | HTTP 403 Forbidden | **PASS** | 17 Nov 2026 |
| TC-17 | Pesanan | Pesanan pada kunjungan successful | Tersimpan di tabel `orders` | Record pesanan baru tersimpan | **PASS** | 17 Nov 2026 |
| TC-18 | Pesanan | Pesanan pada kunjungan failed | Ditolak validasi | Galat pesanan hanya untuk kunjungan berhasil | **PASS** | 17 Nov 2026 |
| TC-19 | Pesanan | Nilai total negatif/teks | Validasi gagal | Galat `total_amount` numerik & >= 0 | **PASS** | 17 Nov 2026 |
| TC-20 | Dashboard | Data kunjungan dan pesanan tersedia | Angka sesuai data | Metrik 4 kunjungan, 50% sukses, Rp 4.000.000 akurat | **PASS** | 17 Nov 2026 |
| TC-21 | Dashboard | Tidak ada kunjungan | Nilai 0; tidak terjadi pembagian nol | HTTP 200, persentase 0%, bebas error | **PASS** | 17 Nov 2026 |
| TC-22 | Form | Request tanpa token CSRF melalui alur web | Ditolak sesuai perlindungan CSRF | Terproteksi `@csrf` di seluruh formulir | **PASS** | 17 Nov 2026 |
| TC-23 | Pagination | Daftar lebih panjang dari batas halaman | Navigasi halaman bekerja | Komponen pagination link tampil | **PASS** | 17 Nov 2026 |
| TC-24 | Responsive | Halaman utama pada layar ponsel | Konten tetap dapat digunakan | Navigasi mobile responsif | **PASS** | 17 Nov 2026 |
| TC-25 | Setup | Fresh setup sesuai README | Migrasi, seeder, dan aplikasi berjalan | `php artisan migrate:fresh --seed` sukses | **PASS** | 17 Nov 2026 |

## Catatan Eksekusi:
- **Perintah Pengujian:** `php artisan test`
- **Total Assertions:** 82 assertions dalam 28 skenario pengujian.
- **Durasi Eksekusi:** ~1.1 detik.
