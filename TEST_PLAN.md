# Test Plan — RuteSales

Catat hasil nyata untuk setiap test: `Pass`, `Fail`, atau `Blocked`, beserta tanggal, penguji, dan bukti. Jangan menandai Pass sebelum dijalankan.

| ID | Area | Skenario | Hasil yang diharapkan |
|---|---|---|---|
| TC-01 | Auth | Membuka dashboard tanpa login | Dialihkan ke login |
| TC-02 | Role | Sales membuka CRUD toko | Ditolak/diarahkan ke halaman yang diizinkan |
| TC-03 | Sales | Admin membuat sales valid | Tersimpan dan muncul di daftar |
| TC-04 | Sales | Nama sales kosong | Validasi gagal, tidak tersimpan |
| TC-05 | Toko | Admin membuat toko valid | Tersimpan dan muncul |
| TC-06 | Toko | Alamat wajib kosong | Validasi gagal |
| TC-07 | Rute | Admin membuat rute dengan sales aktif | Rute tersimpan |
| TC-08 | Rute | Admin menugaskan sales nonaktif | Ditolak validasi |
| TC-09 | Rute | Menambahkan toko nonaktif | Ditolak validasi |
| TC-10 | Rute | Toko sama ditambahkan dua kali pada rute | Ditolak |
| TC-11 | Detail rute | Membuka ID rute yang valid | Detail dan urutan toko tampil |
| TC-12 | Detail rute | Membuka ID yang tidak ada | 404 yang sesuai |
| TC-13 | Kunjungan | Sales mencatat hasil successful | Hasil dan waktu server tersimpan |
| TC-14 | Kunjungan | Hasil failed tanpa alasan | Validasi gagal |
| TC-15 | Kunjungan | Hasil store_closed tanpa alasan | Validasi gagal |
| TC-16 | Otorisasi | Sales mencoba membuka kunjungan sales lain | Akses ditolak |
| TC-17 | Pesanan | Pesanan pada kunjungan successful | Tersimpan |
| TC-18 | Pesanan | Pesanan pada kunjungan failed | Ditolak |
| TC-19 | Pesanan | Nilai total negatif/teks | Validasi gagal |
| TC-20 | Dashboard | Data kunjungan dan pesanan tersedia | Angka sesuai data |
| TC-21 | Dashboard | Tidak ada kunjungan | Nilai 0; tidak terjadi pembagian nol |
| TC-22 | Form | Request tanpa token CSRF melalui alur web | Ditolak sesuai perlindungan CSRF |
| TC-23 | Pagination | Daftar lebih panjang dari batas halaman | Navigasi halaman bekerja |
| TC-24 | Responsive | Halaman utama pada layar ponsel | Konten tetap dapat digunakan |
| TC-25 | Setup | Fresh setup sesuai README | Migrasi, seeder, dan aplikasi berjalan |

## Catatan eksekusi
- Jalankan test otomatis dengan `php artisan test` jika tersedia.
- Untuk uji manual, lengkapi kolom hasil aktual, tanggal, penguji, dan bukti screenshot.
- Jalankan regresi setelah merge fitur.
