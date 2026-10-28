# PRD — Product Requirements Document RuteSales

**Versi:** 1.0-draft | **Status:** Untuk brainstorming dan persetujuan kelompok

## 1. Ringkasan produk
RuteSales adalah aplikasi web internal untuk distributor yang membantu admin/supervisor mengelola data sales dan toko, menyusun rute kunjungan, mencatat hasil kunjungan dan pesanan, serta memantau kinerja melalui dashboard dan laporan.

## 2. Masalah
- Penugasan toko kepada sales sulit dipantau jika dikelola di catatan terpisah.
- Supervisor tidak mudah mengetahui kunjungan yang berhasil, gagal, atau menemukan toko tutup.
- Catatan kunjungan dan pesanan tidak terkumpul dalam satu alur.
- Rekap aktivitas sales membutuhkan perhitungan yang konsisten.

## 3. Tujuan
1. Menyediakan data sales dan toko terpusat.
2. Memungkinkan admin menyusun rute harian berisi toko dengan urutan kunjungan.
3. Memungkinkan sales melihat tugasnya dan mencatat hasil serta waktu check-in.
4. Menghubungkan pesanan dengan kunjungan yang berhasil.
5. Menyediakan dashboard dan laporan berdasarkan data database.
6. Menerapkan konsep Laravel 11 yang dipelajari pada modul praktikum.

## 4. Pengguna
### Admin/Supervisor
- Mengelola data sales dan toko.
- Membuat dan mengubah rute.
- Melihat semua rute, kunjungan, pesanan, dashboard, dan laporan.
- Mengakses fitur sesuai role admin.

### Sales
- Melihat rute dan kunjungan yang ditugaskan kepadanya.
- Melihat detail toko yang perlu dikunjungi.
- Mencatat hasil kunjungan, waktu check-in, dan catatan.
- Wajib memberikan alasan jika toko tutup atau kunjungan gagal.
- Membuat pesanan untuk kunjungan yang memenuhi syarat.
- Tidak boleh melihat/mengubah data kunjungan sales lain.

## 5. Ruang lingkup MVP
### Termasuk
- Login dan logout.
- Role admin dan sales.
- CRUD data sales dan toko, termasuk status aktif/nonaktif.
- Pembuatan rute berdasarkan sales dan tanggal.
- Penambahan toko ke rute dan pengaturan urutan.
- Halaman detail rute dinamis.
- Daftar kunjungan per sales.
- Check-in dan pencatatan hasil kunjungan.
- Pencatatan pesanan sederhana dengan nilai total.
- Dashboard ringkas berdasarkan database.
- Laporan/re kapitulasi sederhana dengan filter tanggal bila waktu memungkinkan.
- Validasi, pagination, seeder/factory, dan test.

### Tidak termasuk pada MVP
- GPS real-time, geofencing, peta navigasi, dan optimasi rute otomatis.
- Integrasi WhatsApp/SMS, pembayaran, ERP, atau aplikasi mobile native.
- Unggah foto bukti kunjungan.
- Inventori produk lengkap dan rincian multi-item pesanan kecuali disepakati sebagai tambahan.
- Notifikasi push dan analitik prediktif.

## 6. Alur utama
1. Pengguna login.
2. Sistem mengarahkan pengguna ke halaman sesuai role.
3. Admin memastikan data sales dan toko tersedia.
4. Admin membuat rute, memilih sales, tanggal, dan toko, lalu mengatur urutan.
5. Sales membuka daftar kunjungan miliknya.
6. Sales mengisi hasil kunjungan; waktu check-in dicatat oleh server.
7. Jika hasil berhasil, sales/pengguna berwenang dapat mencatat satu pesanan terkait kunjungan.
8. Dashboard dan laporan menghitung ringkasan dari data tersimpan.

## 7. Indikator keberhasilan
- Admin dapat membuat rute dengan beberapa toko dan urutan.
- Sales hanya dapat mengakses penugasan miliknya.
- Hasil kunjungan dan waktu check-in tersimpan benar.
- Alasan diwajibkan untuk hasil gagal/toko tutup.
- Pesanan hanya terkait dengan kunjungan yang valid.
- Angka dashboard cocok dengan data uji.
- Aplikasi dapat dijalankan sesuai README pada database pilihan.
- Minimal 10 skenario pengujian dijalankan dan hasilnya dicatat.

## 8. Prinsip produk
- Sederhana dan mudah dipakai di desktop maupun ponsel.
- Status terlihat jelas.
- Data penting tidak hilang hanya karena sales dinonaktifkan.
- Hak akses ditegakkan oleh server.
- Fitur diprioritaskan lebih dahulu daripada ornamen visual.

## 9. Asumsi yang harus dikonfirmasi
- Apakah setiap sales wajib mempunyai akun login?
- Apakah pesanan cukup menyimpan nilai total atau memerlukan detail produk?
- Apakah laporan perlu ekspor CSV/PDF?
- Apakah penghapusan rute diperbolehkan atau hanya pembatalan?
- Apakah ada format laporan khusus dari dosen?
