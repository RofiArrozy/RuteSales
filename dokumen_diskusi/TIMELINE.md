# Timeline RuteSales — Urutan Kerja Berdasarkan Fitur

**Durasi contoh:** 15 hari kerja. Sesuaikan dengan deadline dosen.

| Hari | Rofi — Data Master | Nana — Rute | Ammar — Kunjungan/Pesanan | Arif — Auth/Dashboard |
|---|---|---|---|---|
| 1 | Brainstorming, field sales/toko | Brainstorming alur rute | Brainstorming alur kunjungan | Brainstorming role dan dashboard |
| 2 | Review ERD dan constraints | Review relasi rute/kunjungan | Review relasi kunjungan/pesanan | Setup repository dan kontrak teknis bersama |
| 3 | Siapkan implementasi CRUD sales | Siapkan route/model plan sesuai ERD | Siapkan alur daftar kunjungan | Laravel setup, login/logout dasar, layout skeleton |
| 4 | CRUD sales | Daftar dan form rute | Daftar kunjungan dengan data dummy/kontrak | Role/middleware awal dan reusable components |
| 5 | CRUD toko | Simpan rute dan pilih sales | Detail kunjungan/toko | Layout siap dipakai anggota lain |
| 6 | Validasi dan status master | Pilih toko dan urutan | Check-in dan hasil kunjungan | Proteksi akses dasar dan test auth |
| 7 | Test master data | Detail rute dinamis, cegah duplikasi | Alasan wajib saat gagal/toko tutup | Integrasi awal dan review kontrak |
| 8 | Bug fix master data | Edit rute dan test | Pesanan untuk kunjungan berhasil | Mulai query dashboard dari data tersedia |
| 9 | Search/pagination jika sempat | Test kasus batas dan role | Test ownership dan validasi pesanan | Metrik dashboard dan empty state |
| 10 | Integrasi master data | Integrasi rute | Integrasi kunjungan/pesanan | Laporan/filter periode jika sempat |
| 11 | Test regresi | Test regresi | Test regresi | Test auth/dashboard dan integrasi |
| 12 | Perbaikan bug prioritas tinggi | Perbaikan bug prioritas tinggi | Perbaikan bug prioritas tinggi | Perbaikan bug prioritas tinggi |
| 13 | Dokumentasi fitur | Dokumentasi fitur | Dokumentasi fitur | README/setup dan koordinasi demo |
| 14 | Uji end-to-end bersama | Uji end-to-end bersama | Uji end-to-end bersama | Fresh setup dan uji dashboard |
| 15 | Demo final | Demo final | Demo final | Demo final dan cek rilis |

## Milestone
- **M1 — Hari 2:** keputusan teknis dan ERD disepakati.
- **M2 — Hari 4:** proyek berjalan, layout dan auth dasar tersedia.
- **M3 — Hari 7:** data master dan pembuatan rute sudah bisa diuji.
- **M4 — Hari 10:** alur rute → kunjungan → pesanan terintegrasi.
- **M5 — Hari 12:** bug prioritas tinggi ditangani.
- **M6 — Hari 15:** dokumentasi dan demo final siap.

## Catatan urutan dan paralelisme
Urutan dependency utama adalah data master → rute → kunjungan/pesanan → dashboard/laporan. Namun pekerjaan dapat berjalan paralel jika nama tabel, kolom, relasi, status, dan kontrak data sudah disepakati. Dashboard awal dapat dibuat dengan layout dan placeholder, tetapi metrik final harus menunggu data fitur sebenarnya.
