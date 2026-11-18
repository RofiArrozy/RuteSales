# Git Workflow — RuteSales

## 1. Branch yang disarankan
- `main`: versi stabil untuk demo.
- `develop`: integrasi fitur.
- `feature/master-data`: Anggota 1.
- `feature/routes`: Anggota 2.
- `feature/visits-orders`: Anggota 3.
- `feature/auth-dashboard`: Anggota 4.

## 2. Aturan kolaborasi
- Jangan commit langsung ke `main`.
- Buat commit kecil dengan pesan yang menjelaskan perubahan.
- Pull Request wajib menyebut fitur, file penting, test, dan screenshot jika relevan.
- Minimal satu anggota lain melakukan review.
- Pemilik fitur tidak boleh mengubah ERD/kontrak bersama secara sepihak.
- Perubahan migration atau `routes/web.php` yang menyentuh banyak fitur harus diumumkan.
- Jika ada konflik, diskusikan dahulu; jangan menimpa perubahan anggota lain.

## 3. Siklus kerja
1. Tarik perubahan terbaru dari branch dasar.
2. Buat/pakai branch fitur.
3. Implementasikan task kecil.
4. Jalankan test terkait dan periksa diff.
5. Commit dan push.
6. Buat PR ke `develop`.
7. Anggota lain review dan minta perbaikan jika diperlukan.
8. Merge setelah aplikasi masih dapat dijalankan.
9. Uji regresi pada `develop`.
10. Merge versi stabil ke `main`.

## 4. Contoh
```bash
git checkout develop
git pull origin develop
git checkout -b feature/master-data

# setelah perubahan ditinjau
git status
git diff
git add .
git commit -m "feat: add sales and store management"
git push -u origin feature/master-data
```

## 5. Hindari
- Commit `.env` atau kredensial.
- `git push --force` ke branch bersama tanpa koordinasi.
- Menghapus migration yang sudah dipakai anggota lain.
- Mengubah nama kolom/status tanpa diskusi.
- Menggabungkan kode tanpa menguji alur fitur.
