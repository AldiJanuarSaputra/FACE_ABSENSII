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
   * Pendaftaran siswa baru dengan menginput NIS, Nama, Kelas, Tingkat, dan Jurusan.
   * Proses pemindaian wajah (*face mapping*) untuk mengekstrak struktur landmark wajah unik.
   * Penyimpanan wajah berupa foto *thumbnail* (base64) dan matriks *descriptor* ke database.

3. **Sistem Login Multi-Role (Portal Absensi)**:
   * Hak akses terpisah untuk **Admin/Guru** (untuk manajemen data) dan **Siswa** (untuk memantau absensi pribadi).
   * Fitur pendaftaran akun Admin/Guru baru.

4. **Dashboard Siswa**:
   * Halaman khusus bagi siswa untuk memantau data profil dan riwayat log kehadiran mereka sendiri secara mandiri.

5. **Manajemen Kelas & Jurusan**:
   * Panel Admin untuk mengelola data kelas, tingkat (X, XI, XII), dan jurusan secara fleksibel.
   * Halaman detail kelas untuk melihat daftar siswa di setiap kelas.

6. **Rekapitulasi Absensi**:
   * Laporan riwayat absensi harian yang diperbarui secara dinamis.
   * Statistik ringkasan jumlah kehadiran siswa untuk mempermudah monitoring.

7. **Dukungan Progressive Web App (PWA)**:
   * Mendukung pemasangan aplikasi langsung di perangkat (*Installable App*) dengan dukungan Service Worker offline.

---

## 🛠️ Tech Stack & Arsitektur

* **Frontend**: HTML5, Vanilla CSS3 (Custom Glassmorphism & Neon Gradients), JavaScript (ES6+).
* **AI Engine**: `face-api.min.js` (Model Deteksi Wajah SSD Mobilenet v1 & Face Landmark 68).
* **Backend**: PHP (PDO Object-Oriented).
* **Database**: PostgreSQL (Cloud Hosted di Supabase AWS Pooler).

---

## 📊 Struktur Database (PostgreSQL)

Skema database diimplementasikan menggunakan empat tabel utama:

### 1. Tabel `kelas`
Menyimpan data nama kelas untuk mengelompokkan siswa secara dinamis.
```sql
CREATE TABLE kelas (
    id SERIAL PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Tabel `admin`
Menyimpan data pengguna dengan hak akses admin atau guru.
```sql
CREATE TABLE admin (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin' CHECK (role IN ('admin', 'guru')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Tabel `siswa`
Menyimpan informasi identitas siswa, password untuk login mandiri, dan data *face descriptor* untuk pencocokan wajah.
```sql
CREATE TABLE siswa (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(20),
    kelas_id INTEGER REFERENCES kelas(id) ON DELETE SET NULL,
    tingkat VARCHAR(10),
    jurusan VARCHAR(50),
    wajah TEXT,         -- Menyimpan data gambar thumbnail (base64)
    descriptor TEXT,    -- Menyimpan array 128 dimensi descriptor wajah (JSON)
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4. Tabel `absensi`
Menyimpan riwayat log absensi harian siswa yang teridentifikasi oleh sistem.
```sql
CREATE TABLE absensi (
    id SERIAL PRIMARY KEY,
    nis VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(20),
    tingkat VARCHAR(10),
    jurusan VARCHAR(50),
    tanggal DATE NOT NULL DEFAULT CURRENT_DATE,
    jam TIME NOT NULL DEFAULT CURRENT_TIME,
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📂 Penjelasan Struktur File Proyek

Berikut adalah fungsi dari masing-masing file utama di dalam proyek ini setelah dilakukan restrukturisasi folder:

| Nama File / Path | Deskripsi / Fungsi |
| :--- | :--- |
| `index.php` | Halaman menu utama / dashboard navigasi utama absensi. |
| `absensi.php` | Halaman scan wajah real-time (*Live Camera*) untuk absensi siswa. |
| `register.php` | Halaman registrasi wajah siswa baru beserta pemetaan landmark wajah. |
| `login.php` | Halaman login multi-user (Admin, Guru, Siswa). |
| `logout.php` | Proses keluar akun (destroy session). |
| `register_admin.php` | Halaman khusus untuk meregistrasikan akun Admin/Guru baru. |
| `siswa.php` | Panel manajemen siswa (pencarian, edit, hapus, dan detail). |
| `siswa_dashboard.php` | Halaman riwayat absensi personal siswa yang login secara mandiri. |
| `kelas.php` | Panel manajemen kelas (tambah kelas, kelola tingkat, dan jurusan). |
| `kelas_detail.php` | Halaman rincian daftar siswa berdasarkan kelas yang dipilih. |
| `rekap.php` | Halaman rekapitulasi kehadiran dengan filter pencarian dan statistik. |
| `config/koneksi.php` | File konfigurasi koneksi database PostgreSQL (Supabase) dengan PHP PDO (menggunakan `.env`). |
| `api/simpan_siswa.php` | Endpoint API untuk menyimpan data profil dan descriptor wajah siswa baru. |
| `api/simpan_absen.php` | Endpoint API untuk mencatat log presensi kehadiran siswa. |
| `api/get_siswa.php` | Endpoint API untuk menarik descriptor wajah semua siswa ke client-side. |
| `config/migrate.php` | Skema migrasi untuk memperbarui database secara otomatis. |
| `config/migrate_tingkat_jurusan.php` | Skema migrasi khusus menambahkan kolom tingkat dan jurusan. |
| `manifest.json` | Konfigurasi manifest untuk kemampuan Progressive Web App (PWA). |
| `sw.js` | Service Worker untuk caching aset dan dukungan PWA offline. |
| `config/database.sql` | File SQL mentah yang berisi skema awal basis data. |
| `js/` | Direktori penyimpanan library JavaScript seperti `face-api.min.js`. |
| `models/` | Direktori file bobot (*weights*) model AI FaceAPI.js. |

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

## 👥 Kelompok / Tim Pengembang

Aplikasi ini dikembangkan oleh:

| No | Nama Lengkap | Peran / Kontribusi | Detail Pekerjaan |
| :--- | :--- | :--- | :--- |
| 1 | **Aldi Januar Saputra** | **Database & API Security** | Mengelola integrasi database Supabase PostgreSQL, keamanan konfigurasi, dan file `.env`. |
| 2 | **Desta** | **Authentication System** | Membangun dan mengamankan sistem login serta register akun Admin/Guru (`register_admin.php`). |
| 3 | **Dwi** | **CRUD Siswa & Kelas** | Merancang pengelolaan kelas dan data siswa beserta dataset sampel wajah (`kelas_detail.php`). |
| 4 | **Fiis** | **Face Recognition System** | Integrasi `face-api.js` pada kamera absensi untuk pencocokan wajah real-time. |
| 5 | **Veve** | **Admin Dashboard UI** | Mendesain antarmuka dashboard admin premium (glassmorphism) dan log kehadiran real-time. |
| 6 | **Hasbi** | **Reporting & Export** | Membuat laporan rekapitulasi kehadiran siswa dan fitur ekspor data ke Excel/PDF. |

---

*Dibuat dengan ❤️ oleh Kelompok kami.*

