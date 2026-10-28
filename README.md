# RuteSales — Dokumen Awal Proyek

**Versi:** 1.0-draft  
**Status:** Bahan brainstorming; perlu disetujui seluruh anggota dan dosen  
**Produk:** Sistem Monitoring Kunjungan Sales Lapangan

## Tujuan dokumen
Paket ini menjadi acuan bersama untuk brainstorming dengan AI coding assistant (*vibe coding*), pembagian kerja empat anggota, implementasi, integrasi, dan pengujian. Dokumen bukan pengganti instruksi dosen atau modul praktikum.

## Sumber dan prioritas keputusan
1. Instruksi resmi dosen untuk proyek.
2. Modul praktikum **Laravel 11 Fundamental** yang diberikan.
3. Dokumen dalam paket ini setelah disepakati kelompok.
4. Usulan AI hanya boleh diterapkan setelah ditinjau kelompok.

## Ketentuan wajib proyek
- Laravel 11 dengan pola MVC.
- Routing menggunakan `routes/web.php`.
- Tampilan dirender menggunakan Blade Templating.
- Wajib ada layout bersama dan reusable Blade components.
- Antarmuka menggunakan **Tailwind CSS atau Bootstrap**; pilih satu dan konsisten.
- Database boleh menggunakan **SQLite atau MySQL**, selama konfigurasi berjalan dan fitur dapat diuji.
- Terapkan migration, model dan relasi Eloquent, factory, seeder, form, CSRF, validasi, pagination, dan eager loading sesuai kebutuhan serta materi praktikum.
- Jangan mengganti Laravel/Blade dengan aplikasi frontend terpisah atau halaman statis.
- Fitur harus berfungsi dan diuji; data dashboard tidak boleh sekadar angka hardcode.
- Gunakan autentikasi, pembatasan role, dan pemeriksaan kepemilikan data di sisi server.

## Cara memakai paket
1. Baca `PRD.md` untuk menyepakati produk dan batasan MVP.
2. Baca `SRS.md` untuk kebutuhan fungsional/nonfungsional.
3. Setujui `ERD.md` dan `DECISIONS.md` sebelum migration final.
4. Pakai `TASK_BREAKDOWN.md` untuk membagi pekerjaan berdasarkan fitur, bukan frontend/backend.
5. Gunakan `TIMELINE.md` dan `TODO.md` untuk memantau progres.
6. Ikuti `TECHNICAL_SPEC.md`, `GIT_WORKFLOW.md`, dan `AI_PROMPTS.md`.
7. Jalankan skenario pada `TEST_PLAN.md` dan catat hasil nyata.

## Aturan penting saat vibe coding
- Minta AI mengerjakan satu tugas kecil dalam satu prompt.
- Sertakan dokumen relevan dan sebutkan fitur/branch yang sedang dikerjakan.
- Minta AI memeriksa struktur proyek yang sudah ada sebelum mengubah file.
- Jangan menerima perubahan nama tabel, kolom, status, route name, atau relasi tanpa persetujuan tim.
- Tinjau diff, jalankan aplikasi dan test sebelum menyatakan fitur selesai.
- Jangan membagikan `.env`, password, token, atau data pribadi kepada AI.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP`, atau reset database tanpa persetujuan dan memastikan data aman.

## Keputusan yang belum final
Lihat `DECISIONS.md`. Semua keputusan terbuka harus disepakati kelompok sebelum implementasi yang bergantung padanya.


## Pembagian anggota yang berlaku
- Rofi — Manajemen Data Master (sales dan toko).
- Nana — Manajemen Rute Kunjungan.
- Ammar — Kunjungan Lapangan dan Pesanan.
- Arif — Akses Pengguna, Dashboard, dan Laporan.

Lihat `TASK_BREAKDOWN.md` untuk cakupan rinci dan `NOTES.md` untuk urutan kerja, ketergantungan fitur, aturan AI coding, dan integrasi.
