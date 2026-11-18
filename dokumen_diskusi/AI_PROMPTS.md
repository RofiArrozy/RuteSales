# AI Prompt Pack — Brainstorming dan Vibe Coding RuteSales

## Cara pakai
Sebelum meminta kode, berikan `README.md`, `PRD.md`, `SRS.md`, `ERD.md`, dan bagian fitur yang relevan. Minta AI mengerjakan satu task yang jelas, bukan seluruh aplikasi sekaligus.

## 1. Brainstorming kelompok
> Baca PRD.md, SRS.md, ERD.md, dan DECISIONS.md. Kami kelompok beranggotakan empat orang dan membangun RuteSales dengan Laravel 11, MVC, Blade, layout dan reusable components. Tolong bantu brainstorming fitur, risiko, dan skenario pengguna. Bedakan hal yang tertulis di dokumen dengan usulan baru. Jangan mengubah requirement atau skema secara diam-diam. Keluarkan pertanyaan terbuka yang harus diputuskan tim sebelum coding.

## 2. Konteks umum untuk AI coding assistant
> Kamu membantu mengembangkan RuteSales. Ikuti dokumen proyek yang disertakan. Stack wajib Laravel 11 + MVC + Blade; CSS dipilih konsisten antara Tailwind CSS atau Bootstrap; database dipilih antara SQLite atau MySQL. Periksa struktur proyek sebelum mengubah kode. Jangan mengganti nama tabel, kolom, status, route name, relasi, atau kontrak antarf fitur tanpa meminta persetujuan. Gunakan validasi server-side, CSRF, otorisasi role dan kepemilikan, serta test. Kerjakan hanya scope yang diminta. Setelah selesai, laporkan file yang diubah, alasan, perintah yang dijalankan, skenario uji, dan hal yang belum diverifikasi.

## 3. Minta implementasi satu fitur
> Implementasikan fitur **[NAMA FITUR]** pada branch **[NAMA BRANCH]** sesuai PRD.md, SRS.md, ERD.md, dan TECHNICAL_SPEC.md. Sebelum coding, periksa kode saat ini dan tuliskan rencana file/route/model/controller/view/validasi/otorisasi/test yang perlu dibuat atau diubah. Jika ada konflik dokumen atau kode, berhenti dan jelaskan pilihannya. Jangan menyentuh fitur lain yang tidak diperlukan. Setelah implementasi, jalankan test yang relevan jika lingkungan memungkinkan dan laporkan hasil sebenarnya.

## 4. Prompt untuk brainstorming ERD
> Review ERD.md untuk RuteSales. Cari entitas yang kurang, relasi ambigu, aturan delete yang berisiko, constraint yang perlu ditambahkan, dan risiko duplikasi data. Jangan langsung menulis migration. Sajikan setiap perubahan sebagai: masalah, pilihan, konsekuensi, rekomendasi, dan pertanyaan untuk kelompok. Tandai mana requirement eksplisit dan mana usulan.

## 5. Prompt debugging
> Bantu debug error Laravel berikut: [TEMPEL ERROR LENGKAP]. Konteks tindakan sebelum error: [LANGKAH]. Periksa stack trace dan file terkait, jangan menebak. Berikan diagnosis dan perubahan terkecil. Jangan menghapus fitur atau mengubah versi dependency tanpa alasan. Setelah perbaikan, jelaskan cara memverifikasi.

## 6. Prompt code review
> Review perubahan fitur ini terhadap PRD.md, SRS.md, ERD.md, dan TECHNICAL_SPEC.md. Cari bug logika, validasi yang kurang, mass assignment, CSRF, kebocoran akses antar-role, akses sales ke data sales lain, query N+1, pagination, status tidak konsisten, dan penanganan data kosong. Urutkan temuan berdasarkan tingkat keparahan. Jangan melakukan refactor besar; berikan saran minimal dengan lokasi file yang relevan.

## 7. Prompt integrasi branch
> Periksa integrasi branch [BRANCH FITUR] dengan branch integrasi. Bandingkan migration, nama tabel/kolom, relasi, route name, middleware, parameter controller, view, status, dan bentuk data. Jangan mengubah kontrak bersama tanpa persetujuan. Laporkan konflik dan rekomendasi sebelum mengedit.

## 8. Prompt test
> Buat test untuk fitur [FITUR] berdasarkan acceptance criteria di SRS.md. Sertakan skenario normal, input tidak valid, belum login, role salah, data tidak ditemukan, dan ownership. Gunakan pola testing Laravel yang sesuai. Jangan mengklaim test lulus jika belum dijalankan.

## 9. Aturan keselamatan vibe coding
- Satu prompt berisi satu task kecil yang dapat diperiksa.
- Simpan commit sebelum refactor besar.
- Tinjau diff sebelum menerima perubahan AI.
- Jangan menempelkan `.env`, password, token, atau data pribadi.
- Jangan menjalankan reset/drop database tanpa memahami dampaknya dan persetujuan tim.
- AI boleh memberi saran perubahan ERD; kelompok yang memutuskan.
- Jangan menyatakan fitur selesai sebelum diuji di browser dan/atau test yang sesuai.
