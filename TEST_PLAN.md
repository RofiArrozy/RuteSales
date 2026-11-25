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

## Skenario tambahan hasil audit (otomatis, PHPUnit dan Node)
| Kode | Area | Skenario | Ekspektasi |
|---|---|---|---|
| TC-26 | Auth | Akun nonaktif mencoba login | Ditolak; sesi aktif akun yang dinonaktifkan dihentikan |
| TC-27 | Auth | 6 kali login gagal dalam semenit | Dibatasi (throttle) |
| TC-28 | Rute | Hapus rute yang sudah punya check-in | Ditolak; status rute tidak berubah |
| TC-29 | Rute | Ubah daftar toko setelah check-in pertama | Ditolak (daftar toko terkunci) |
| TC-30 | Admin | Admin check-in atau mengubah check-in | HTTP 403 |
| TC-31 | Klarifikasi | Admin meminta klarifikasi pada kunjungan sudah check-in | Tersimpan, sales melihat banner, tercatat di audit log |
| TC-32 | Klarifikasi | Sales menjawab tanpa isi jawaban | Validasi gagal |
| TC-33 | Klarifikasi | Sales menjawab pada tanggal lain saat klarifikasi terbuka | Diizinkan; tanpa klarifikasi tetap ditolak |
| TC-34 | Alamat | Kecamatan tidak cocok dengan kelurahan yang dipilih | Validasi gagal |
| TC-35 | Alamat | Nama wilayah tertempel di kolom jalan | Dibuang otomatis; alamat tidak ganda |
| TC-36 | Alamat | Edit toko lama tanpa menyentuh alamat | Tersimpan; alamat lama dipertahankan |
| TC-37 | Alamat (JS) | Pencarian koordinat: hasil di kota lain, hasil kosong, HTTP error | Hasil jauh dibuang, lanjut ke kueri berikutnya, error dilempar |
| TC-38 | Laporan | Filter toko, hasil, dan tanggal tidak valid | Data sesuai filter; tanggal tidak valid kembali ke bawaan |
| TC-39 | Laporan | Ekspor CSV | BOM UTF-8, header, filter dihormati, rumus dinetralkan, hanya admin |
| TC-40 | Akses | Sales membuka foto atau kunjungan sales lain | HTTP 403 melalui Policy |

Catatan: tes browser (dropdown berjenjang, pin peta, pencarian jalan) dijalankan manual dan lewat Chromium headless dengan layanan peta ditiru; Nominatim asli dan GPS ponsel diuji manual.
