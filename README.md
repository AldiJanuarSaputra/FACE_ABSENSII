# 📸 Sistem Absensi Face ID (Pengenalan Wajah)

[![PHP Version](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Supabase-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://supabase.com/)
[![JS](https://img.shields.io/badge/JavaScript-FaceAPI.js-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://github.com/justadudewhohacks/face-api.js/)
[![Aesthetic](https://img.shields.io/badge/Design-Glassmorphism-FF1493?style=for-the-badge&logo=css3&logoColor=white)](#)

Aplikasi Web Absensi berbasis **Pengenalan Wajah (Face Recognition)** real-time menggunakan pustaka **FaceAPI.js** (berbasis TensorFlow.js) pada sisi *client* dan **PHP** dengan database **PostgreSQL (Supabase)** pada sisi *server*. 

Aplikasi ini didesain dengan antarmuka **Premium Dark Theme** yang modern, menggunakan efek **Glassmorphism**, gradasi neon merah muda/ungu yang memukau, serta mikro-animasi yang interaktif.

---

## ✨ Fitur Utama

1. **Scan Absensi Wajah (Live Camera)**:
   * Deteksi wajah real-time melalui kamera/webcam.
   * Pencocokan wajah instan berdasarkan *descriptor vector* (128 dimensi) dari database.
   * Penandaan status kehadiran secara otomatis (Hadir / Lambat) berdasarkan jam masuk siswa.

2. **Daftar & Registrasi Wajah**:
   * Pendaftaran siswa baru dengan menginput NIS, Nama, dan Kelas.
   * Proses pemindaian wajah (*face mapping*) untuk mengekstrak struktur landmark wajah unik.
   * Penyimpanan wajah berupa foto *thumbnail* (base64) dan matriks *descriptor* ke database.

3. **Kelola Data Siswa**:
   * Panel admin untuk menampilkan daftar siswa yang telah terdaftar lengkap beserta foto wajah mereka.
   * Dilengkapi fitur pencarian cepat serta hapus data siswa secara real-time.

4. **Rekapitulasi Absensi**:
   * Laporan riwayat absensi harian yang diperbarui secara dinamis.
   * Statistik ringkasan jumlah kehadiran siswa untuk mempermudah monitoring.

---

## 🛠️ Tech Stack & Arsitektur

* **Frontend**: HTML5, Vanilla CSS3 (Custom Glassmorphism & Neon Gradients), JavaScript (ES6+).
* **AI Engine**: `face-api.min.js` (Model Deteksi Wajah SSD Mobilenet v1 & Face Landmark 68).
* **Backend**: PHP (PDO Object-Oriented).
* **Database**: PostgreSQL (Cloud Hosted di Supabase AWS Pooler).

---

## 📊 Struktur Database (PostgreSQL)

Skema database diimplementasikan menggunakan dua tabel utama:

### 1. Tabel `siswa`
Menyimpan informasi identitas siswa dan data *face descriptor* untuk pencocokan wajah.
```sql
CREATE TABLE siswa (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20) UNIQUE,
    nama VARCHAR(100),
    kelas VARCHAR(20),
    wajah TEXT,         -- Menyimpan data gambar thumbnail (base64)
    descriptor TEXT     -- Menyimpan array 128 dimensi descriptor wajah (JSON)
);
```

### 2. Tabel `absensi`
Menyimpan riwayat log absensi harian siswa yang teridentifikasi oleh sistem.
```sql
CREATE TABLE absensi (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20),
    nama VARCHAR(100),
    kelas VARCHAR(20),
    tanggal DATE,
    jam TIME,
    status VARCHAR(20)  -- 'Hadir' atau 'Lambat'
);
```

---

## 📂 Penjelasan Struktur File Proyek

Berikut adalah fungsi dari masing-masing file utama di dalam proyek ini:

| Nama File | Deskripsi / Fungsi |
| :--- | :--- |
| `index.php` | Halaman menu utama / navigasi dashboard absensi. |
| `absensi.php` | Halaman untuk memindai wajah siswa menggunakan kamera (*Scan Absensi*). |
| `register.php` | Halaman untuk meregistrasikan wajah dan identitas siswa baru. |
| `siswa.php` | Panel kelola data siswa terdaftar (menampilkan daftar, pencarian, dan hapus). |
| `rekap.php` | Halaman rekapitulasi data kehadiran siswa. |
| `koneksi.php` | Konfigurasi koneksi database PostgreSQL (Supabase) via PHP PDO. |
| `simpan_siswa.php` | API server untuk menyimpan data siswa baru ke database. |
| `simpan_absen.php` | API server untuk mencatat log absensi siswa ke database. |
| `get_siswa.php` | API server untuk mengambil seluruh data *descriptor* wajah untuk proses pencocokan. |
| `database.sql` | Skema struktur tabel database SQL. |
| `js/` | Folder berisi pustaka `face-api.min.js`. |
| `models/` | Folder berisi file-file weights model AI untuk mendeteksi wajah dan landmark. |

---

## 💻 Cara Menjalankan Aplikasi Secara Lokal (XAMPP)

1. Pastikan aplikasi **XAMPP** sudah terinstal di komputer Anda.
2. Letakkan folder proyek `Face_absensi` di dalam direktori `htdocs` XAMPP Anda:
   * Jalur default: `C:\xampp\htdocs\Face_absensi`
   * *(Atau gunakan Directory Junction jika diletakkan di luar folder tersebut)*.
3. Buka **XAMPP Control Panel** dan klik **Start** pada modul **Apache**.
4. Buka browser favorit Anda dan akses URL berikut:
   👉 **`http://localhost/Face_absensi/index.php`**

---

*Dibuat dengan ❤️ oleh **Aldi Januar Saputra***

