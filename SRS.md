# Software Requirements Specification (SRS) — RuteSales

## 1. Deskripsi Umum
**RuteSales** adalah aplikasi berbasis web yang dirancang untuk distributor barang guna memantau, mengelola, dan mengevaluasi aktivitas kunjungan tim sales lapangan ke toko dan warung mitra secara terstruktur. Sistem mengintegrasikan penugasan rute harian multi-toko, pencatatan hasil check-in di lapangan, serta pemantauan pesanan dan kinerja melalui dashboard operasional.

---

## 2. Kebutuhan Fungsional (Functional Requirements)

| ID | Kebutuhan Fungsional | PIC |
| :--- | :--- | :--- |
| **FR-01** | Pengguna dapat melakukan login dan logout dengan aman. | Arif |
| **FR-02** | Sistem membatasi akses halaman dan aksi berdasarkan peran: **Admin** dan **Sales**. | Arif |
| **FR-03** | Admin dapat mengelola (CRUD) profil Sales dan mengaktifkan/menonaktifkan statusnya. | Rofi |
| **FR-04** | Admin dapat mengelola (CRUD) data Toko/Warung dan mengaktifkan/menonaktifkan statusnya. | Rofi |
| **FR-05** | Admin dapat membuat rute penugasan baru dengan menentukan sales aktif, tanggal, dan nama rute. | Nana |
| **FR-06** | Admin dapat menambahkan beberapa toko aktif ke dalam satu rute dan mengatur urutan kunjungan (`sequence`). | Nana |
| **FR-07** | Sistem mencegah penambahan toko yang sama lebih dari satu kali dalam rute yang sama. | Nana |
| **FR-08** | Sistem menyediakan halaman detail rute dinamis (`/rute/detail/{id}`) yang merender daftar toko berurutan. | Nana |
| **FR-09** | Sales dapat melihat daftar penugasan pada halaman khusus **Kunjungan Saya** yang hanya memuat tugas miliknya. | Ammar |
| **FR-10** | Sales dapat melakukan check-in dan mencatat hasil kunjungan (`successful`, `store_closed`, `failed`). | Ammar |
| **FR-11** | Sistem mencatat waktu check-in (`checked_in_at`) berdasarkan waktu server (*server-time*). | Ammar |
| **FR-12** | Sistem mewajibkan pengisian alasan kegagalan (`failure_reason`) jika hasil kunjungan `failed` atau `store_closed`. | Ammar |
| **FR-13** | Pesanan hanya dapat dibuat untuk kunjungan dengan hasil `successful` (maksimal 1 pesanan per kunjungan pada MVP). | Ammar |
| **FR-14** | Sales dicegah untuk mengakses atau mengubah rute/kunjungan milik sales lain (*data ownership protection*). | Ammar |
| **FR-15** | Dashboard Admin menampilkan metrik ringkasan dari basis data: Total Kunjungan Selesai, Persentase Keberhasilan, dan Total Nilai Pesanan. | Arif |
| **FR-16** | Dashboard menangani kondisi data kosong (*empty state*) tanpa memicu kesalahan pembagian dengan nol (*division by zero*). | Arif |
| **FR-17** | Seluruh formulir yang memanipulasi data dilindungi token CSRF dan divalidasi di sisi server. | Semua |
| **FR-18** | Sistem menyediakan laporan rekapitulasi data kunjungan dan transaksi dengan filter periode tanggal. | Arif |

---

## 3. Aturan Bisnis (Business Rules)
1. **Otorisasi Data:** Hanya Admin yang dapat mengelola data master dan membuat rute penugasan. Sales hanya berhak mencatat hasil kunjungan dan pesanan pada rute yang ditugaskan kepadanya.
2. **Kondisi Data Master:** Hanya sales berstatus aktif dan toko berstatus aktif yang dapat dipilih saat membuat rute penugasan baru.
3. **Integritas Rute:** Satu rute dapat memuat lebih dari satu toko yang disusun berdasarkan urutan kunjungan (`sequence`). Toko tidak boleh diduplikasi dalam rute yang sama.
4. **Pencatatan Kunjungan:** Waktu check-in tidak boleh dimanipulasi oleh klien browser, melainkan otomatis dicatat menggunakan stempel waktu server saat formulir dikirim.
5. **Keterkaitan Pesanan:** Pesanan tidak dapat dibuat apabila kunjungan berstatus gagal (`failed`) atau toko tutup (`store_closed`).
6. **Preservasi Data:** Toko atau Sales yang telah memiliki riwayat penugasan/kunjungan tidak boleh dihapus secara permanen (*hard delete*), melainkan dialihkan statusnya menjadi nonaktif (*inactive*).

---

## 4. Kebutuhan Non-Fungsional
- **Teknologi:** PHP 8.2+, Laravel 11.x MVC, Blade Templating, Tailwind CSS, SQLite.
- **Keamanan:** Hash password menggunakan Bcrypt/Argon2, proteksi CSRF di seluruh form POST/PUT/DELETE, sanitasi input untuk pencegahan XSS dan SQL Injection.
- **Kinerja:** Penerapan *eager loading* Eloquent (`with`) untuk mencegah masalah N+1 query, serta pagination pada daftar data master dan rute.
- **Kesesuaian Pengujian:** Dilengkapi minimal 25 skenario uji otomatis (*automated feature tests*) yang dapat dieksekusi melalui `php artisan test`.
