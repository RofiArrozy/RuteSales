# Pembagian Jobdesk — RuteSales (4 Anggota)

**Prinsip:** pembagian berdasarkan fitur, bukan pembagian frontend/backend. Setiap pemilik fitur bertanggung jawab atas alur fitur secara utuh: route, controller, model/relasi yang dibutuhkan, Blade view, validasi, otorisasi, dan pengujian. Semua tetap mengikuti struktur Laravel 11 MVC, Blade, layout, dan reusable components.

## Anggota 1 — Rofi: Manajemen Data Master
**Branch:** `feature/master-data`

### Cakupan fitur
- CRUD data sales.
- CRUD data toko.
- Status aktif/nonaktif untuk sales dan toko.
- Validasi formulir dan pesan error.
- Pencarian/pagination jika waktu memungkinkan.
- Factory/seeder data master melalui koordinasi dengan tim.
- Test fitur master data dan dokumentasi singkat.

### Urutan pengerjaan
1. Baca PRD, SRS, ERD, TECHNICAL_SPEC, dan NOTES.
2. Konfirmasi field sales/toko dan relasi dengan `users` kepada tim.
3. Tunggu/koordinasikan migration serta nama model yang disepakati.
4. Implementasikan daftar dan detail data jika diperlukan.
5. Implementasikan tambah dan simpan sales/toko.
6. Implementasikan edit dan update sales/toko.
7. Implementasikan status aktif/nonaktif; hindari penghapusan permanen jika data punya riwayat.
8. Tambahkan validasi server-side, pesan error, dan CSRF pada form.
9. Tambahkan pagination/pencarian jika masuk prioritas.
10. Uji skenario valid, input salah, data kosong, dan akses non-admin.
11. Buat PR dan minta review.

### Kriteria selesai
Admin dapat menambah, melihat, mengubah, dan menonaktifkan sales/toko. Data tersimpan di database, validasi berjalan, dan fitur tidak merusak relasi yang dipakai fitur rute.

## Anggota 2 — Nana: Manajemen Rute Kunjungan
**Branch:** `feature/routes`

### Cakupan fitur
- Daftar rute.
- Membuat dan mengubah rute.
- Memilih sales aktif dan tanggal rute.
- Memilih toko aktif untuk rute.
- Mengatur urutan toko.
- Detail rute dinamis.
- Status rute sesuai aturan bisnis yang disepakati.
- Validasi, pencegahan duplikasi, test, dan dokumentasi.

### Urutan pengerjaan
1. Baca PRD, SRS, ERD, TECHNICAL_SPEC, dan NOTES.
2. Pastikan kontrak model/tabel `Sales`, `Store`, `SalesRoute`, dan `RouteVisit` sudah disepakati.
3. Pastikan data sales dan toko dapat dipilih dari fitur Rofi.
4. Implementasikan daftar rute dengan pagination jika dibutuhkan.
5. Implementasikan form pembuatan rute.
6. Implementasikan penyimpanan rute dengan sales aktif dan tanggal valid.
7. Implementasikan pemilihan beberapa toko aktif beserta urutan kunjungan.
8. Cegah duplikasi toko pada rute yang sama dan pastikan urutan unik.
9. Implementasikan detail dinamis berdasarkan ID.
10. Implementasikan edit rute sesuai batasan status yang disepakati.
11. Uji rute valid, sales/toko nonaktif, duplikasi, ID tidak ditemukan, dan role salah.
12. Buat PR dan minta review.

### Kriteria selesai
Admin dapat membuat rute untuk sales aktif, menambahkan toko aktif tanpa duplikasi, menentukan urutan, serta melihat detail rute yang benar.

## Anggota 3 — Ammar: Kunjungan Lapangan dan Pesanan
**Branch:** `feature/visits-orders`

### Cakupan fitur
- Halaman Kunjungan Saya untuk sales.
- Detail kunjungan/toko.
- Check-in dan hasil kunjungan.
- Catatan dan alasan jika kunjungan gagal atau toko tutup.
- Pencatatan pesanan sederhana.
- Perlindungan agar sales hanya dapat mengakses penugasan miliknya.
- Validasi, test, dan dokumentasi.

### Urutan pengerjaan
1. Baca PRD, SRS, ERD, TECHNICAL_SPEC, dan NOTES.
2. Pastikan alur autentikasi dan relasi sales-user sudah disepakati.
3. Pastikan rute beserta item kunjungan sudah tersedia dari fitur Nana.
4. Implementasikan daftar kunjungan yang hanya menampilkan rute milik sales login.
5. Implementasikan halaman detail kunjungan/toko.
6. Implementasikan form check-in dan hasil kunjungan.
7. Simpan waktu check-in dari server; jangan percaya waktu yang dikirim browser.
8. Wajibkan alasan/catatan untuk hasil `failed` atau `store_closed`.
9. Implementasikan pembuatan pesanan hanya untuk kunjungan `successful`.
10. Validasi nilai pesanan dan batasi maksimal satu pesanan per kunjungan jika keputusan MVP tetap berlaku.
11. Uji akses ke ID milik sales lain, status yang tidak valid, dan pesanan untuk kunjungan gagal.
12. Buat PR dan minta review.

### Kriteria selesai
Sales melihat kunjungannya sendiri, mencatat hasil dengan waktu server, memberikan alasan ketika gagal/toko tutup, dan hanya dapat mencatat pesanan pada kunjungan yang berhasil.

## Anggota 4 — Arif: Akses Pengguna, Dashboard, dan Laporan
**Branch:** `feature/auth-dashboard`

### Cakupan fitur
- Login/logout dan redirect sesuai role.
- Middleware/otorisasi admin dan sales.
- Layout Blade utama dan navigasi bersama.
- Reusable components.
- Dashboard dari data aktual database.
- Laporan rekap dan filter periode jika waktu memungkinkan.
- Test, dokumentasi setup, dan koordinasi integrasi.

### Urutan pengerjaan
1. Baca PRD, SRS, ERD, TECHNICAL_SPEC, dan NOTES.
2. Bersama tim, sepakati autentikasi, role, dan relasi akun sales ke profil sales.
3. Siapkan login/logout dan middleware/otorisasi dasar.
4. Buat layout Blade utama yang dapat dipakai semua anggota.
5. Buat reusable components bersama: alert, form error, status badge, page header.
6. Dokumentasikan cara anggota lain memakai layout dan components.
7. Implementasikan redirect dan pembatasan route berdasarkan role.
8. Setelah fitur kunjungan/pesanan mulai menyediakan data, buat query dashboard.
9. Tampilkan metrik yang dihitung dari database dan tangani kondisi tanpa data.
10. Buat laporan/filter periode jika waktu memungkinkan.
11. Uji login, role, dashboard, pembagian nol, dan data kosong.
12. Koordinasikan integrasi dan README final; jangan mengambil alih seluruh pengujian anggota lain.

### Kriteria selesai
Login/logout dan role bekerja; semua halaman dapat menggunakan layout/components bersama; dashboard menghitung data aktual dengan benar; laporan tersedia sesuai scope yang disetujui.

## Urutan ketergantungan fitur
1. Semua anggota: kunci PRD/SRS/ERD dan keputusan teknis.
2. Arif mengoordinasikan fondasi: Laravel, database, layout, autentikasi/role dasar.
3. Rofi: selesaikan data sales dan toko.
4. Nana: susun rute dari data sales/toko.
5. Ammar: bangun kunjungan dan pesanan berdasarkan rute.
6. Arif: selesaikan dashboard/laporan dari data aktual.
7. Semua anggota: integrasi, pengujian end-to-end, dokumentasi, dan demo.

Lihat `NOTES.md` untuk langkah rinci per tahap.

## Tugas bersama
- Menyetujui PRD, SRS, ERD, status, nama kolom, route name, dan CSS/database.
- Membuat migration, model, dan relasi bersama atau melalui pemilik yang ditunjuk tanpa perubahan sepihak.
- Review minimal satu PR anggota lain.
- Menjalankan test end-to-end dan regresi.
- Menyiapkan dokumentasi, demo, dan bukti fitur.

## Definition of Done
- [ ] Alur fitur berjalan melalui browser.
- [ ] Validasi server-side dan CSRF diterapkan sesuai kebutuhan.
- [ ] Hak akses dan kepemilikan data diuji.
- [ ] Data berasal dari database, bukan hardcode.
- [ ] Empty state dan error state ditangani.
- [ ] Test/skenario manual dicatat.
- [ ] Tidak merusak fitur anggota lain.
- [ ] PR direview sebelum merge.
