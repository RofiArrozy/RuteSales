# SRS — Software Requirements Specification RuteSales

**Versi:** 1.0-draft

## 1. Tujuan
Dokumen ini mendefinisikan perilaku yang harus dimiliki RuteSales agar anggota kelompok dan AI coding assistant membangun fitur dengan pemahaman yang sama.

## 2. Persyaratan fungsional

| ID | Persyaratan | Prioritas |
|---|---|---|
| FR-01 | Pengguna dapat login dan logout. | Must |
| FR-02 | Sistem membatasi halaman berdasarkan role admin/sales. | Must |
| FR-03 | Admin dapat membuat, melihat, mengubah, dan menonaktifkan data sales. | Must |
| FR-04 | Admin dapat membuat, melihat, mengubah, dan menonaktifkan data toko. | Must |
| FR-05 | Admin dapat membuat dan mengubah rute dengan sales, tanggal, nama, dan status. | Must |
| FR-06 | Admin dapat menambahkan toko ke rute dan menentukan urutan kunjungan. | Must |
| FR-07 | Sistem menyediakan detail rute dinamis berdasarkan ID. | Must |
| FR-08 | Sales dapat melihat daftar kunjungan dari rute yang ditugaskan kepadanya. | Must |
| FR-09 | Sales dapat mengirim hasil kunjungan dan catatan; waktu check-in berasal dari server. | Must |
| FR-10 | Alasan wajib diisi jika hasil kunjungan `failed` atau `store_closed`. | Must |
| FR-11 | Pesanan dapat dibuat untuk kunjungan berhasil yang valid; satu kunjungan maksimal satu pesanan pada MVP. | Must |
| FR-12 | Dashboard menampilkan jumlah kunjungan, persentase berhasil, dan nilai pesanan berdasarkan database. | Must |
| FR-13 | Input divalidasi di server dan kesalahan ditampilkan dengan jelas. | Must |
| FR-14 | Daftar data panjang memakai pagination; relasi yang ditampilkan dimuat secara efisien. | Should |
| FR-15 | Migration, factory, dan seeder tersedia untuk setup dan pengujian. | Must |
| FR-16 | Admin dapat melihat rekap kunjungan dan pesanan berdasarkan periode. | Should |
| FR-17 | Form yang mengubah data dilindungi CSRF. | Must |
| FR-18 | Sistem mencegah toko yang sama ditambahkan dua kali ke satu rute. | Must |

## 3. Aturan bisnis
- Hanya admin yang dapat mengelola data master dan menyusun rute.
- Sales hanya dapat melihat dan mengubah kunjungan yang dimiliki rute yang ditugaskan kepadanya.
- Hanya sales aktif yang boleh ditugaskan ke rute baru.
- Hanya toko aktif yang boleh ditambahkan ke rute baru.
- Setiap item kunjungan pada rute memiliki urutan yang unik dalam rute tersebut.
- Hasil kunjungan yang didukung: `successful`, `store_closed`, `failed`.
- `store_closed` dan `failed` wajib mempunyai alasan/catatan yang menjelaskan.
- Check-in dicatat menggunakan waktu server, bukan menerima waktu check-in bebas dari browser.
- Pesanan hanya boleh dikaitkan dengan kunjungan `successful`.
- Pada MVP, satu kunjungan menghasilkan maksimal satu pesanan.
- Status dan enum disepakati di `DECISIONS.md`; jangan membuat variasi ejaan status antarfitur.
- Nonaktifkan sales/toko yang telah mempunyai riwayat daripada menghapus secara permanen, kecuali ada keputusan lain.

## 4. Persyaratan nonfungsional
- **Platform:** Laravel 11, PHP yang kompatibel, Blade.
- **CSS:** Tailwind CSS atau Bootstrap; satu pilihan konsisten.
- **Database:** SQLite atau MySQL.
- **Keamanan:** hash password menggunakan mekanisme Laravel; CSRF; validasi server; otorisasi per role dan kepemilikan.
- **Kegunaan:** label form jelas, status mudah dibedakan, layout responsif.
- **Pemeliharaan:** MVC, nama file konsisten, reusable components, migration, seeder, dan README.
- **Kinerja:** pagination dan eager loading saat relasi diperlukan untuk tabel/daftar.
- **Keandalan:** dashboard menangani data kosong dan tidak membagi dengan nol.

## 5. Kriteria penerimaan global
- Fresh setup sesuai README dapat menjalankan aplikasi.
- Pengguna tanpa login tidak dapat membuka halaman internal.
- Sales tidak dapat mengakses data milik sales lain dengan mengubah URL/ID.
- Validasi mencegah data tidak valid tersimpan.
- Semua alur utama dapat diuji dari UI.
- Minimal 10 test case dijalankan, bukan hanya direncanakan.
