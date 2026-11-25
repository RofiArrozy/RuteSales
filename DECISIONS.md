# Decision Log — RuteSales

Gunakan dokumen ini dalam sesi brainstorming. Isi kolom keputusan dan tanggal setelah kelompok menyepakati. Jangan biarkan keputusan penting hanya tersimpan dalam chat AI.

| ID | Topik | Pilihan/pertanyaan | Rekomendasi awal | Status |
|---|---|---|---|---|
| D-01 | CSS | Tailwind atau Bootstrap? | Tailwind selaras dengan materi, Bootstrap juga diizinkan dosen | Belum diputuskan |
| D-02 | Database | SQLite atau MySQL? | Pilih satu yang bisa dijalankan seluruh anggota; MySQL cocok bila tim sudah memakainya | Belum diputuskan |
| D-03 | Akun sales | Apakah setiap profil sales harus punya akun? | Ya jika semua sales memakai fitur Kunjungan Saya | Perlu konfirmasi |
| D-04 | Pesanan | Total nominal saja atau detail produk? | Total nominal saja untuk menjaga MVP sederhana | Belum diputuskan |
| D-05 | Hasil kunjungan | Daftar status final? | `successful`, `store_closed`, `failed` | Usulan |
| D-06 | Status rute | Daftar status final? | `planned`, `in_progress`, `completed`, `cancelled` | Usulan |
| D-07 | Pesanan | Satu atau banyak pesanan per kunjungan? | Maksimal satu pesanan per kunjungan pada MVP | Usulan |
| D-08 | Laporan | Perlu ekspor CSV/PDF? | Tidak untuk MVP kecuali diminta dosen | Diputuskan: PDF dan CSV tersedia (lihat README bagian 6D) |
| D-09 | Hapus data | Hapus permanen atau nonaktifkan? | Nonaktifkan sales/toko yang sudah memiliki riwayat | Usulan |
| D-10 | Timeline | Durasi pengerjaan | 15 hari kerja sebagai baseline | Perlu disesuaikan |
| D-11 | Hak admin | Apakah admin boleh mengubah hasil check-in sales? | Tidak; admin hanya memantau dan meminta klarifikasi | Diputuskan (opsi C) |
| D-12 | Hapus rute | Rute yang sudah ada check-in dihapus atau dibatalkan? | Ditolak; ubah status lewat Edit Rute | Diputuskan |
| D-13 | Alamat toko | Teks bebas atau dropdown wilayah? | Dropdown provinsi sampai kelurahan + kolom jalan; pin dari pencarian bertahap | Diputuskan |
| D-14 | Daftar toko pada rute | Boleh diubah setelah sales mulai check-in? | Tidak; terkunci setelah check-in pertama | Diputuskan |
| D-15 | Versi PHP | Batas versi PHP pada `composer.json` | Dibiarkan `^8.5` (mengikuti Herd) | Diputuskan, tinjau ulang bila lingkungan berubah |

## Catatan rapat brainstorming
- Tanggal:
- Peserta:
- Keputusan yang disetujui:
- Pertanyaan untuk dosen:
- Perubahan dokumen yang harus dilakukan:
