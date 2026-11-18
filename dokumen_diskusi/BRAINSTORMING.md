# Panduan Brainstorming AI — RuteSales

## Tujuan sesi
Menyamakan pemahaman empat anggota tentang masalah yang diselesaikan, pengguna, alur kerja, batasan MVP, struktur data, dan pembagian tanggung jawab sebelum meminta AI menulis kode.

## Agenda 45–60 menit
1. **5 menit — Masalah:** apa kesulitan yang ingin diselesaikan RuteSales?
2. **10 menit — Pengguna dan alur:** admin menyusun rute; sales melaporkan kunjungan; dashboard merekap hasil.
3. **10 menit — Scope:** pisahkan fitur wajib dan fitur tambahan.
4. **10 menit — ERD dan aturan bisnis:** sales, toko, rute, kunjungan, pesanan.
5. **10 menit — Pembagian kerja:** pastikan tiap anggota memiliki fitur yang bisa didemokan.
6. **5–15 menit — Risiko dan keputusan:** tandai hal yang belum jelas dan catat di `DECISIONS.md`.

## Pertanyaan yang perlu dijawab kelompok
- Siapa yang akan memakai aplikasi dan dari perangkat apa?
- Apakah semua sales harus memiliki akun?
- Apa yang dianggap sebagai kunjungan berhasil?
- Apakah alasan wajib saat toko tutup/gagal?
- Apakah pesanan cukup berupa nominal total?
- Metrik apa yang harus terlihat di dashboard?
- Apakah dosen mewajibkan laporan atau ekspor?
- Database dan CSS framework apa yang akan dipakai?
- Apa bukti penerapan konsep dari modul yang akan ditunjukkan saat demo?

## Prompt brainstorming awal
> Kami empat mahasiswa sedang mengembangkan RuteSales, sistem monitoring kunjungan sales lapangan. Baca PRD.md, SRS.md, ERD.md, TASK_BREAKDOWN.md, dan DECISIONS.md. Bantu kami menilai apakah MVP realistis untuk dikerjakan oleh empat orang. Jangan menambah fitur besar. Bedakan requirement dari usulan. Berikan: (1) alur pengguna, (2) risiko fitur, (3) pertanyaan yang harus diputuskan, (4) dependency antaranggota, (5) urutan demo end-to-end. Jangan menghasilkan kode dulu.

## Output rapat yang wajib disimpan
- ERD yang disetujui.
- Daftar status final.
- Pilihan CSS dan database.
- Pembagian fitur per anggota.
- Definition of Done.
- Timeline yang disesuaikan deadline nyata.
