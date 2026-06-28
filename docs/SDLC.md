# System Development Life Cycle (SDLC) - FACE_ABSENSII

Sistem Absensi Deteksi Wajah (FACE_ABSENSII) dikembangkan menggunakan metodologi Agile/Waterfall yang disesuaikan untuk kebutuhan kolaborasi tim:

## 1. Analisis Kebutuhan (Requirement Analysis)
- **Kebutuhan Fungsional**:
  - Registrasi & Login Admin secara aman.
  - CRUD Manajemen Kelas dan Data Siswa.
  - Upload dataset sampel wajah untuk setiap siswa.
  - Deteksi dan verifikasi wajah real-time menggunakan kamera/webcam.
  - Rekapitulasi absensi otomatis harian dan bulanan.
  - Ekspor laporan kehadiran ke Excel (CSV) dan PDF.
- **Kebutuhan Non-Fungsional**:
  - Deteksi wajah yang cepat (< 2 detik per wajah).
  - Skema warna dashboard yang modern (premium dark mode/glassmorphism).
  - Penyimpanan data terpusat di PostgreSQL via Supabase.

## 2. Desain Sistem (System Design)
- **Database**: Skema relasional menggunakan PostgreSQL Supabase yang mencakup tabel `admin`, `kelas`, `siswa`, dan `absensi`.
- **Frontend**: PHP Native, Tailwind CSS / Custom CSS modern, Javascript, dan Chart.js untuk dashboard.
- **Deteksi Wajah**: Menggunakan library client-side `face-api.js` yang memanfaatkan model neural network berbasis TensorFlow.js.

## 3. Implementasi (Implementation)
Pengembangan dibagi menjadi beberapa modul utama yang dikerjakan secara paralel oleh tim pengembang:
- **Keamanan & DB**: Aldi
- **Autentikasi**: Desta
- **CRUD Siswa/Kelas**: Dwi
- **Face Recognition**: Fiis
- **Dashboard UI**: Veve
- **Reporting**: Hasbi

## 4. Pengujian (Testing)
- Pengujian fungsionalitas tombol CRUD.
- Pengujian akurasi deteksi wajah di berbagai kondisi pencahayaan.
- Verifikasi keamanan session halaman admin.
