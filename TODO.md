# TODO Checklist — RuteSales

Gunakan checklist ini sebagai papan kerja kelompok. Tandai selesai hanya setelah implementasi diuji.

## Fase 0 — Brainstorming dan keputusan
- [ ] Semua anggota membaca README, PRD, SRS, ERD.
- [ ] Konfirmasi ruang lingkup MVP kepada dosen jika diperlukan.
- [ ] Putuskan Tailwind CSS **atau** Bootstrap.
- [ ] Putuskan SQLite **atau** MySQL.
- [ ] Putuskan apakah sales wajib punya akun.
- [ ] Putuskan struktur pesanan: total saja atau item produk.
- [ ] Sepakati daftar status rute dan hasil kunjungan.
- [ ] Setujui ERD dan constraints.
- [ ] Tentukan nama branch dan aturan Pull Request.

## Fase 1 — Fondasi
- [ ] Buat repository dan branch integrasi.
- [ ] Siapkan Laravel 11 dan catat versi PHP/dependency.
- [ ] Konfigurasi database.
- [ ] Buat migration awal.
- [ ] Jalankan migration dari kondisi database kosong.
- [ ] Buat model dan relasi Eloquent.
- [ ] Buat factory dan seeder data demo.
- [ ] Siapkan layout Blade utama.
- [ ] Siapkan reusable components.
- [ ] Siapkan autentikasi dan role.

## Fase 2 — Fitur
### Data Master
- [ ] CRUD sales.
- [ ] CRUD toko.
- [ ] Status aktif/nonaktif.
- [ ] Validasi dan pesan error.
- [ ] Search/pagination bila waktu cukup.

### Rute
- [ ] Daftar rute.
- [ ] Buat/edit rute.
- [ ] Pilih sales aktif.
- [ ] Tambah toko aktif.
- [ ] Atur urutan kunjungan.
- [ ] Cegah toko ganda dalam rute.
- [ ] Detail rute dinamis.

### Kunjungan dan Pesanan
- [ ] Sales melihat kunjungan miliknya.
- [ ] Check-in mencatat waktu server.
- [ ] Pilih hasil kunjungan.
- [ ] Alasan wajib untuk failed/store_closed.
- [ ] Pesanan hanya untuk kunjungan berhasil.
- [ ] Validasi total nominal.
- [ ] Cegah akses ke kunjungan sales lain.

### Dashboard dan Laporan
- [ ] Total kunjungan dari database.
- [ ] Jumlah kunjungan berhasil/gagal/toko tutup.
- [ ] Persentase berhasil.
- [ ] Total nilai pesanan.
- [ ] Data kosong ditangani.
- [ ] Filter periode/laporan jika waktu cukup.

## Fase 3 — Uji dan rilis
- [ ] Minimal 10 skenario test dijalankan.
- [ ] Uji role admin dan sales.
- [ ] Uji input tidak valid.
- [ ] Uji akses ID milik sales lain.
- [ ] Uji data kosong.
- [ ] Uji responsive di desktop dan ponsel.
- [ ] Periksa query relasi dan N+1.
- [ ] Pastikan tidak ada secret di repository.
- [ ] README mencakup setup, migrate, seed, run, test.
- [ ] Fresh setup diuji anggota lain.
- [ ] Siapkan akun demo aman untuk presentasi.
- [ ] Siapkan screenshot dan alur demo.
