# Rancangan Pembagian Kerja & GitHub Issues
**Proyek: Sistem Absensi Deteksi Wajah (FACE_ABSENSII)**
*Dokumen ini digunakan sebagai panduan tugas masing-masing anggota kelompok dan panduan pembuatan GitHub Issues & Project Board.*

---

## 👥 Anggota Kelompok & Pembagian Peran

| Nama Anggota | Peran / Fitur Utama | Deskripsi Detail Pekerjaan |
| :--- | :--- | :--- |
| **Aldi** | **Database & API Security** | Mengelola integrasi database Supabase PostgreSQL, konfigurasi variabel lingkungan di `.env`, dan mengamankan file koneksi (`config/koneksi.php`). |
| **Desta** | **Authentication System** | Membangun sistem registrasi admin baru dan login admin (`register_admin.php`) dengan proteksi password hashing. |
| **Dwi** | **CRUD Siswa & Kelas** | Merancang fitur manajemen kelas dan data siswa, termasuk upload dataset foto siswa (`kelas_detail.php`). |
| **Fiis** | **Face Recognition System** | Mengintegrasikan library `face-api.js` pada halaman absensi kamera untuk deteksi dan pencocokan wajah secara real-time. |
| **Veve** | **Admin Dashboard UI** | Mendesain antarmuka dashboard admin yang modern (glassmorphism/premium dark mode) dan log absensi siswa real-time. |
| **Hasbi** | **Reporting & Export** | Membuat fitur rekapitulasi data kehadiran siswa dan fitur cetak laporan ke PDF/Excel (CSV). |

---

## 🛠️ Draf GitHub Issues (Tinggal Copy-Paste)

Gunakan draf di bawah ini saat membuat Issue baru di tab **Issues > New Issue** pada repositori GitHub kelompok.

### 1. Issue Aldi
*   **Judul:** `[Feature] Integrasi Database Supabase & Pengamanan Konfigurasi API`
*   **Deskripsi:**
    ```markdown
    ### Deskripsi Tugas
    Melakukan integrasi database Supabase PostgreSQL secara aman, memastikan koneksi berjalan lancar, dan mengelola variabel lingkungan melalui file `.env`.

    ### Checklist Tugas:
    - [ ] Pastikan file `config/koneksi.php` terintegrasi dengan benar menggunakan Supabase.
    - [ ] Kelola variabel kredensial database di `.env` dan pastikan tidak ter-upload ke repositori publik (gunakan `.gitignore`).
    - [ ] Buat skema tabel database absensi, siswa, kelas, dan admin yang optimal di PostgreSQL Supabase.
    ```

### 2. Issue Desta
*   **Judul:** `[Feature] Sistem Registrasi & Login Admin yang Aman`
*   **Deskripsi:**
    ```markdown
    ### Deskripsi Tugas
    Membangun sistem autentikasi admin (`register_admin.php` dan form login) dengan keamanan enkripsi password yang standar.

    ### Checklist Tugas:
    - [ ] Gunakan fungsi `password_hash()` (bcrypt) untuk enkripsi password saat registrasi admin.
    - [ ] Buat sistem verifikasi login session agar halaman dashboard tidak bisa diakses tanpa login.
    - [ ] Berikan feedback error yang jelas jika email atau password salah saat login.
    ```

### 3. Issue Dwi
*   **Judul:** `[Feature] CRUD Manajemen Kelas dan Detail Siswa`
*   **Deskripsi:**
    ```markdown
    ### Deskripsi Tugas
    Membuat fitur pengelolaan data kelas dan data siswa yang terhubung dengan database (`kelas_detail.php`).

    ### Checklist Tugas:
    - [ ] Buat tampilan tabel data kelas dan tombol aksi CRUD (Tambah, Edit, Hapus).
    - [ ] Buat form input detail siswa beserta foto sampel wajah untuk keperluan training Face-API.
    - [ ] Implementasikan pencarian data siswa dan filter berdasarkan kelas.
    ```

### 4. Issue Fiis
*   **Judul:** `[Feature] Integrasi Face-API.js untuk Deteksi & Pencocokan Wajah`
*   **Deskripsi:**
    ```markdown
    ### Deskripsi Tugas
    Mengintegrasikan library `face-api.js` pada kamera absensi untuk mendeteksi wajah siswa secara real-time dan mencocokkannya dengan database.

    ### Checklist Tugas:
    - [ ] Muat model deteksi wajah (Face Landmark, Recognition, Expression) secara asinkron.
    - [ ] Hubungkan feed kamera webcam dengan canvas overlay untuk menampilkan bounding box wajah.
    - [ ] Kirim hasil pencocokan wajah yang sukses ke API backend presensi untuk mencatat kehadiran.
    ```

### 5. Issue Veve
*   **Judul:** `[UI/UX] Desain Dashboard Admin & Monitor Absensi Real-time`
*   **Deskripsi:**
    ```markdown
    ### Deskripsi Tugas
    Merancang UI dashboard utama admin yang modern, bersih (glassmorphism/premium dark mode), dan menampilkan statistik kehadiran siswa secara langsung.

    ### Checklist Tugas:
    - [ ] Tampilkan kartu statistik ringkas (Jumlah Siswa, Hadir Hari Ini, Terlambat, Alfa).
    - [ ] Tambahkan grafik persentase kehadiran (bisa menggunakan Chart.js).
    - [ ] Buat tabel/log aktivitas kehadiran terbaru yang ter-update otomatis secara real-time.
    ```

### 6. Issue Hasbi
*   **Judul:** `[Feature] Fitur Rekapitulasi Absensi & Ekspor Laporan`
*   **Deskripsi:**
    ```markdown
    ### Deskripsi Tugas
    Membuat fitur rekapitulasi data absensi siswa berdasarkan periode tanggal atau kelas tertentu, serta menyediakan tombol ekspor data.

    ### Checklist Tugas:
    - [ ] Buat halaman rekap absensi dengan filter Tanggal Mulai, Tanggal Selesai, dan Kelas.
    - [ ] Hitung total kehadiran, keterlambatan, dan ketidakhadiran per siswa.
    - [ ] Implementasikan fitur ekspor laporan ke format Excel (CSV) atau cetak PDF.
    ```

---

## 🗂️ Panduan Project Board (Kanban) di GitHub

Untuk memantau progress secara visual, ikuti langkah berikut:
1. Masuk ke halaman repositori di GitHub.
2. Pilih tab **Projects** -> **New Project** -> Pilih template **Board** (Kanban).
3. Buat tiga kolom utama:
   - 🟥 **Todo**: Tempat menyimpan semua Issue yang baru dibuat.
   - 🟨 **In Progress**: Pindahkan kartu ke sini jika sedang dikerjakan oleh anggota terkait.
   - 🟩 **Done**: Pindahkan kartu ke sini jika pengerjaan sudah selesai dan kodenya sudah di-push/merge.
