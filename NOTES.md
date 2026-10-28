# NOTES.md — Urutan Pengerjaan RuteSales dari Awal sampai Akhir

**Anggota kelompok**
- Rofi — Data Master (sales dan toko)
- Nana — Manajemen Rute Kunjungan
- Ammar — Kunjungan Lapangan dan Pesanan
- Arif — Autentikasi, Dashboard, dan Laporan

Dokumen ini menjelaskan urutan kerja berdasarkan ketergantungan fitur. Ini bukan pembagian frontend/backend.

## Peta urutan singkat

1. **Semua anggota:** brainstorming, scope, keputusan teknis, ERD.
2. **Semua anggota, dikoordinasikan Arif:** repository, Laravel 11, database, migration inti, model, relasi, layout bersama.
3. **Arif:** login/logout dan role dasar; anggota lain boleh menyiapkan fitur tetapi pengujian akses perlu menunggu auth siap.
4. **Rofi:** CRUD sales dan toko.
5. **Nana:** membuat rute menggunakan data sales dan toko dari Rofi.
6. **Ammar:** daftar kunjungan sales, check-in, hasil kunjungan, dan pesanan menggunakan rute dari Nana.
7. **Arif:** menyelesaikan dashboard dan laporan dengan data kunjungan/pesanan yang nyata.
8. **Semua anggota:** integrasi, pengujian end-to-end, dokumentasi, dan demo.

Tahap 3–6 dapat berjalan sebagian paralel setelah kontrak data disepakati, tetapi alur fitur harus mengikuti ketergantungan tersebut.

---

## Langkah 1 — Brainstorming dan kunci kebutuhan
**PIC:** Semua anggota  
**Mulai:** Hari 1

- [ ] Baca `README.md`, `PRD.md`, `SRS.md`, `ERD.md`, `TECHNICAL_SPEC.md`.
- [ ] Sepakati fitur MVP yang benar-benar dibuat.
- [ ] Putuskan Tailwind CSS atau Bootstrap.
- [ ] Putuskan SQLite atau MySQL.
- [ ] Sepakati apakah semua sales memiliki akun login.
- [ ] Sepakati status rute dan hasil kunjungan.
- [ ] Sepakati bentuk pesanan: total nominal saja atau rincian produk.
- [ ] Sepakati aturan edit/batal rute dan penghapusan data.
- [ ] Catat keputusan pada `DECISIONS.md`.

**Output sebelum lanjut:** keputusan penting tidak lagi berbeda antaranggota.

## Langkah 2 — Finalisasi ERD dan kontrak data
**PIC:** Semua anggota; Arif mengoordinasikan pencatatan  
**Mulai:** Hari 1–2

- [ ] Finalisasi tabel `users`, `sales`, `stores`, `sales_routes`, `route_visits`, dan `orders`.
- [ ] Sepakati nama kolom, tipe data, foreign key, unique constraint, dan status.
- [ ] Sepakati relasi akun `users` ke profil `sales`.
- [ ] Sepakati relasi rute ke sales, kunjungan ke rute/toko, dan pesanan ke kunjungan.
- [ ] Sepakati nama model, route name, dan nama status.
- [ ] Perbarui `ERD.md` dan `TECHNICAL_SPEC.md`.

**Output sebelum lanjut:** semua anggota menggunakan skema dan nama yang sama. Jangan membuat migration terpisah dengan asumsi masing-masing.

## Langkah 3 — Setup proyek dan fondasi Laravel
**PIC utama:** Arif mengoordinasikan; semua anggota ikut memverifikasi  
**Mulai:** Hari 2–3

- [ ] Buat repository Git, branch `main` dan `develop`.
- [ ] Siapkan Laravel 11 dan konfigurasi database.
- [ ] Pastikan setiap anggota bisa menjalankan proyek secara lokal.
- [ ] Buat migration inti berdasarkan ERD yang disetujui.
- [ ] Buat model dan relasi Eloquent.
- [ ] Buat factory dan seeder data demo.
- [ ] Buat layout Blade utama dan reusable components.
- [ ] Buat login/logout dan role dasar.
- [ ] Tulis instruksi setup di README.

**Output sebelum lanjut:** aplikasi berjalan, database bisa dimigrasi, layout dapat dipakai, dan autentikasi dasar tersedia.

Catatan: migration/model dapat ditugaskan kepada satu PIC yang disepakati. Jangan sampai semua anggota mengedit migration inti secara terpisah tanpa koordinasi.

## Langkah 4 — Rofi mengerjakan Data Master
**PIC:** Rofi  
**Mulai:** Setelah skema tabel disepakati; target Hari 3–6

Urutan internal:
1. [ ] CRUD sales: daftar, tambah, simpan, edit, update.
2. [ ] CRUD toko: daftar, tambah, simpan, edit, update.
3. [ ] Status aktif/nonaktif.
4. [ ] Validasi server-side, CSRF, dan pesan error.
5. [ ] Pencarian/pagination jika waktu cukup.
6. [ ] Uji input valid, input salah, data kosong, dan role yang tidak berhak.
7. [ ] Buat Pull Request dan minta review.

**Output yang wajib tersedia untuk Nana:** data sales aktif dan toko aktif dapat dipilih dari aplikasi.

## Langkah 5 — Nana mengerjakan Rute Kunjungan
**PIC:** Nana  
**Mulai:** Setelah kontrak data tersedia; gunakan data master Rofi saat integrasi

Urutan internal:
1. [ ] Daftar rute.
2. [ ] Form membuat rute: sales, tanggal, nama, catatan jika digunakan.
3. [ ] Validasi bahwa sales yang dipilih aktif.
4. [ ] Tambahkan toko aktif ke rute.
5. [ ] Simpan urutan kunjungan dan cegah duplikasi toko dalam satu rute.
6. [ ] Buat halaman detail rute dinamis.
7. [ ] Implementasikan edit/status rute sesuai aturan yang disepakati.
8. [ ] Uji data tidak valid, duplikasi, ID tidak ditemukan, dan role salah.
9. [ ] Buat Pull Request dan minta review.

**Output yang wajib tersedia untuk Ammar:** rute memiliki daftar kunjungan yang terhubung dengan sales dan toko.

## Langkah 6 — Ammar mengerjakan Kunjungan dan Pesanan
**PIC:** Ammar  
**Mulai:** Setelah alur rute tersedia

Urutan internal:
1. [ ] Pastikan hubungan akun login → profil sales → rute → kunjungan berfungsi.
2. [ ] Buat halaman Kunjungan Saya yang hanya menampilkan tugas sales login.
3. [ ] Buat detail kunjungan/toko.
4. [ ] Buat check-in dan pemilihan hasil kunjungan.
5. [ ] Catat waktu check-in dari server.
6. [ ] Wajibkan alasan/catatan untuk hasil gagal atau toko tutup.
7. [ ] Cegah sales mengubah kunjungan milik sales lain.
8. [ ] Buat form pesanan dengan nilai total.
9. [ ] Izinkan pesanan hanya untuk kunjungan berhasil.
10. [ ] Terapkan batas satu pesanan per kunjungan jika keputusan MVP itu disetujui.
11. [ ] Uji validasi, ownership, dan kondisi kunjungan tidak valid.
12. [ ] Buat Pull Request dan minta review.

**Output yang wajib tersedia untuk Arif:** data kunjungan dan pesanan yang tersimpan di database untuk dashboard.

## Langkah 7 — Arif menyelesaikan Dashboard dan Laporan
**PIC:** Arif  
**Mulai:** Layout/auth dapat dibuat sejak awal; metrik final setelah data kunjungan/pesanan tersedia

Urutan internal:
1. [ ] Selesaikan login/logout, redirect role, dan proteksi halaman.
2. [ ] Pastikan semua halaman memakai layout dan reusable components bersama.
3. [ ] Buat dashboard dengan query data aktual.
4. [ ] Tampilkan jumlah kunjungan, hasil kunjungan, persentase keberhasilan, dan total nilai pesanan.
5. [ ] Tangani kondisi data kosong dan hindari pembagian dengan nol.
6. [ ] Buat laporan dan filter periode jika masuk scope/waktu.
7. [ ] Cocokkan angka dashboard dengan data uji.
8. [ ] Dokumentasikan setup, akun demo, dan alur demo.
9. [ ] Buat Pull Request dan minta review.

**Output:** dashboard/laporan sesuai data aktual, bukan angka hardcode.

## Langkah 8 — Integrasi fitur
**PIC:** Semua anggota; Arif mengoordinasikan integrasi  
**Target:** Hari 10–11

- [ ] Setiap pemilik fitur membuka PR.
- [ ] Anggota lain memeriksa perubahan.
- [ ] Gabungkan fitur ke `develop` satu per satu.
- [ ] Selesaikan konflik migration, route, controller, dan view.
- [ ] Jalankan migration dari database kosong.
- [ ] Jalankan seeder.
- [ ] Pastikan semua halaman memakai layout bersama.
- [ ] Jalankan aplikasi setelah setiap merge.

**Jangan:** menggabungkan semua kode sekaligus tanpa tes, atau mengubah kontrak database secara sepihak.

## Langkah 9 — Pengujian end-to-end dan regresi
**PIC:** Semua anggota  
**Target:** Hari 11–13

Uji alur utama:
1. Admin login.
2. Admin membuat sales dan toko.
3. Admin membuat rute untuk sales dengan beberapa toko.
4. Sales login.
5. Sales melihat rute miliknya.
6. Sales mencatat check-in dan hasil kunjungan.
7. Sales mencatat alasan ketika gagal/toko tutup.
8. Sales mencatat pesanan untuk kunjungan berhasil.
9. Admin melihat angka dashboard yang sesuai.
10. Uji sales tidak dapat mengakses data sales lain.

Tambahan:
- [ ] Uji input tidak valid.
- [ ] Uji data kosong dan ID tidak ditemukan.
- [ ] Uji role tidak berhak.
- [ ] Uji pagination dan eager loading sesuai kebutuhan.
- [ ] Catat hasil aktual di `TEST_PLAN.md`.
- [ ] Jalankan `php artisan test` jika test otomatis tersedia.

## Langkah 10 — Dokumentasi dan persiapan demo
**PIC:** Semua anggota; Arif mengoordinasikan README  
**Target:** Hari 13–15

- [ ] Pastikan README memuat persyaratan, setup, konfigurasi database, migrate/seed, run, dan test.
- [ ] Pastikan `.env` tidak di-commit.
- [ ] Minta anggota lain menjalankan aplikasi dari setup baru.
- [ ] Siapkan data demo dan screenshot.
- [ ] Siapkan demo dengan alur admin → rute → sales → kunjungan → pesanan → dashboard.
- [ ] Perbaiki bug prioritas tinggi sebelum menambah fitur baru.
- [ ] Pastikan setiap anggota memahami fitur yang menjadi tanggung jawabnya.

---

## Timeline ringkas

| Hari | Fokus utama | PIC utama |
|---|---|---|
| 1 | Brainstorming dan scope | Semua |
| 2 | ERD, keputusan teknis, kontrak data | Semua; Arif koordinasi |
| 3 | Setup Laravel, database, layout/auth dasar | Arif koordinasi; semua verifikasi |
| 3–6 | CRUD sales dan toko | Rofi |
| 5–8 | Rute dan urutan toko | Nana |
| 7–10 | Kunjungan dan pesanan | Ammar |
| 8–10 | Dashboard final dan laporan | Arif |
| 10–11 | Integrasi branch | Semua; Arif koordinasi |
| 11–13 | Pengujian dan perbaikan bug | Semua |
| 13–15 | Dokumentasi dan demo | Semua |

Timeline dapat tumpang tindih karena anggota boleh menyiapkan kode secara paralel. Namun, fitur yang bergantung pada data anggota lain harus diuji bersama setelah output dependensinya tersedia.

## Aturan kerja AI / vibe coding

Gunakan prompt kecil, sertakan dokumen relevan, dan sebutkan nama anggota serta branch. Minta AI memeriksa kode yang sudah ada sebelum mengubahnya. Setelah perubahan:
- periksa diff,
- jalankan test,
- uji lewat browser,
- catat file yang berubah,
- jangan menyatakan lulus jika belum diuji.

Jangan membagikan `.env`, password, token, atau data pribadi. Jangan menjalankan perintah reset database seperti `migrate:fresh` atau `db:wipe` tanpa persetujuan tim dan memastikan data aman.

## Catatan keputusan rapat
- Tanggal:
- Peserta:
- Keputusan yang disepakati:
- Pertanyaan untuk dosen:
- Perubahan ERD/dokumen:
- PIC dan deadline berikutnya:
