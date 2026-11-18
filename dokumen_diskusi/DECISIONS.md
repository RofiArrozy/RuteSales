# Decision Log — RuteSales

Dokumen ini memuat keputusan teknis dan ruang lingkup yang disepakati oleh kelompok untuk pengembangan sistem RuteSales.

| ID | Topik | Pilihan/pertanyaan | Keputusan Final | Status | Tanggal Disepakati |
|---|---|---|---|---|---|
| D-01 | CSS | Tailwind atau Bootstrap? | Tailwind CSS selaras dengan materi modul | Disetujui | 17 Nov 2026 |
| D-02 | Database | SQLite atau MySQL? | SQLite (ringan dan konsisten di seluruh lingkungan tim) | Disetujui | 17 Nov 2026 |
| D-03 | Akun sales | Apakah setiap profil sales harus punya akun? | Ya, setiap sales memiliki akun login untuk fitur Kunjungan Saya | Disetujui | 17 Nov 2026 |
| D-04 | Pesanan | Total nominal saja atau detail produk? | Total nominal saja (`order_number`, `total_amount`) untuk menjaga batasan MVP | Disetujui | 17 Nov 2026 |
| D-05 | Hasil kunjungan | Daftar status final? | `successful`, `store_closed`, `failed` | Disetujui | 17 Nov 2026 |
| D-06 | Status rute | Daftar status final? | `planned`, `in_progress`, `completed`, `cancelled` | Disetujui | 17 Nov 2026 |
| D-07 | Pesanan per kunjungan | Satu atau banyak pesanan per kunjungan? | Maksimal 1 pesanan per kunjungan (relasi 1:1 `orders` ke `route_visits`) | Disetujui | 17 Nov 2026 |
| D-08 | Laporan | Lingkup laporan? | Rekapitulasi dashboard metrik nyata & filter laporan per periode tanggal | Disetujui | 17 Nov 2026 |
| D-09 | Hapus data | Hapus permanen atau nonaktifkan? | Nonaktifkan (`status = 'inactive'`) untuk sales/toko yang memiliki riwayat | Disetujui | 17 Nov 2026 |
| D-10 | Timeline | Durasi pengerjaan | Dilaksanakan bertahap per modul/PIC (Rofi, Nana, Ammar, Arif) | Disetujui | 17 Nov 2026 |

## Catatan Rapat Brainstorming
- **Tanggal:** 17 November 2026
- **Peserta:** Rofi, Nana, Ammar, Arif
- **Keputusan Utama:** Mengembangkan skema database menjadi 6 entitas relasional, menerapkan hak akses berbasis peran (Admin vs Sales), pembatasan akses kepemilikan data kunjungan, dan pencatatan pesanan sederhana.
