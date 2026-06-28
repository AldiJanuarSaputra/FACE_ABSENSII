# Tim Pengembang - FACE_ABSENSII

Berikut adalah susunan tim pengembang Proyek Sistem Absensi Deteksi Wajah beserta tanggung jawab masing-masing anggota:

## 👥 Profil & Peran Tim

### 1. Aldi (Database & API Security)
- **Tanggung Jawab**: Integrasi database Supabase PostgreSQL, pengelolaan `.env`, keamanan file `config/koneksi.php`, serta proteksi kredensial API.

### 2. Desta (Authentication System)
- **Tanggung Jawab**: Pembuatan fitur registrasi admin baru (`register_admin.php`), sistem login, pengamanan session, dan enkripsi password menggunakan `password_hash()`.

### 3. Dwi (CRUD Siswa & Kelas)
- **Tanggung Jawab**: Desain modul manajemen data kelas, form detail siswa, input data diri siswa, serta manajemen dataset gambar sampel wajah.

### 4. Fiis (Face Recognition System)
- **Tanggung Jawab**: Integrasi library `face-api.js`, setup webcam feed, tracking wajah real-time, loading weights model, dan pencocokan data wajah ke backend.

### 5. Veve (Admin Dashboard UI)
- **Tanggung Jawab**: Merancang layout antarmuka dashboard utama, implementasi tema glassmorphism & premium dark mode, visualisasi grafik data kehadiran, dan antarmuka responsif.

### 6. Hasbi (Reporting & Export)
- **Tanggung Jawab**: Pembuatan rekap absensi otomatis, fitur filter berdasarkan tanggal/kelas, ekspor laporan ke format Excel (CSV) dan PDF.
