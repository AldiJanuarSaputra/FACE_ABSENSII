# 🚀 Rencana Pengembangan Proyek Berbasis Fitur (Feature-Driven Roadmap)
## 📸 Sistem Absensi Face ID (Pengenalan Wajah) - Skala Kolaborasi (6 Anggota)

Dokumen ini adalah cetak biru (*blueprint*) kerja kolaborasi tim untuk masa depan. Agar koordinasi antar **6 anggota tim** berjalan lancar tanpa tumpang tindih kode (*code conflict*), pengerjaan dibagi secara terstruktur **berdasarkan FITUR utama** yang akan didevelop:

---

## 👥 PEMBAGIAN TUGAS DAN FITUR UTAMA (FEATURE BREAKDOWN)

```mermaid
graph TD
    F1[Fitur 1: Keamanan & Env] --> F2[Fitur 2: AI Anti-Spoofing]
    F2 --> F3[Fitur 3: PWA Mobile]
    F3 --> F4[Fitur 4: Dashboard Grafik]
    F4 --> F5[Fitur 5: Ekspor Laporan]
    F5 --> F6[Fitur 6: QA & Rilis]
```

---

### 🔒 FITUR 1: Sistem Keamanan & Autentikasi Admin-Guru
Fitur ini berfokus pada pengamanan database Supabase, pencegahan SQL Injection, dan pembatasan hak akses halaman kelola data agar tidak bisa ditembus sembarang orang.
* **Penanggung Jawab (PIC)**: **[Anggota 4] (Backend Engineer)** & **Aldi Januar Saputra (PM/Database)**
* **Cabang Git (*Branch*)**: `backend-security`
* **Daftar Tugas (TODO)**:
  - [ ] Memisahkan kredensial Supabase dari `koneksi.php` menggunakan library `php-dotenv` via file `.env`.
  - [ ] Membuat halaman login Admin & Guru yang aman dengan enkripsi password (`password_hash`).
  - [ ] Implementasi manajemen *session* di PHP agar halaman `siswa.php` dan `rekap.php` tidak bisa diakses langsung via URL tanpa login.
  - [ ] Melakukan sanitasi input seluruh formulir menggunakan Prepared Statements (PDO).

---

### 👁️ FITUR 2: Deteksi Keaktifan Wajah (Anti-Spoofing / Liveness Detection)
Meningkatkan akurasi AI dan mencegah kecurangan absensi siswa menggunakan foto cetak atau layar HP.
* **Penanggung Jawab (PIC)**: **[Anggota 3] (AI Specialist)** & **Veve (UI/UX)**
* **Cabang Git (*Branch*)**: `ai-engine`
* **Daftar Tugas (TODO)**:
  - [ ] Mengimplementasikan logika deteksi kedipan mata (*eye-blinking detection*) atau deteksi jarak pergerakan landmark wajah (*head movement*) pada `absensi.php` menggunakan model 68 Landmark `face-api.js`.
  - [ ] Optimasi caching browser lokal untuk model AI SSD Mobilenet v1 agar kamera terbuka instan tanpa delay mengunduh ulang model.
  - [ ] Mendesain indikator garis pemindaian wajah (*bounding box*) real-time yang estetis pada UI.

---

### 📱 FITUR 3: PWA Mobile & Responsive Design
Mengubah aplikasi web menjadi Progressive Web App agar bisa diinstal di Android/iOS layaknya aplikasi asli dengan tampilan premium yang ramah layar smartphone.
* **Penanggung Jawab (PIC)**: **Veve (UI/UX & PWA Engineer)**
* **Cabang Git (*Branch*)**: `veve`
* **Daftar Tugas (TODO)**:
  - [ ] Menyusun file `manifest.json` dan *Service Worker* javascript untuk caching aset CSS, JS, dan gambar secara offline.
  - [ ] Mendesain ulang layout CSS `absensi.php` dan `register.php` menjadi *Mobile-First Design* menggunakan Glassmorphism dan warna neon.
  - [ ] Membuat *splash screen* (layar pemuatan) dan ikon aplikasi khusus saat diinstal di HP.

---

### 📊 FITUR 4: Dashboard Analitik Grafik (Chart.js)
Membuat halaman statistik kehadiran harian dan bulanan yang interaktif bagi admin untuk mempermudah monitoring siswa.
* **Penanggung Jawab (PIC)**: **[Anggota 5] (Analytics Engineer)**
* **Cabang Git (*Branch*)**: `analytics-report`
* **Daftar Tugas (TODO)**:
  - [ ] Integrasi library **Chart.js** pada halaman utama dashboard admin.
  - [ ] Membuat grafik kehadiran harian (Hadir vs Lambat) dan persentase kehadiran per kelas.
  - [ ] Membuat widget ringkasan statistik (Total Siswa Terdaftar, Hadir Hari Ini, Terlambat Hari Ini, Tanpa Keterangan).

---

### 📥 FITUR 5: Ekspor Laporan Otomatis (Excel & PDF)
Menyediakan fitur sekali klik bagi guru/admin untuk mengunduh laporan kehadiran resmi untuk kebutuhan administrasi.
* **Penanggung Jawab (PIC)**: **[Anggota 5] (Analytics)** & **[Anggota 6] (QA/Documentation)**
* **Cabang Git (*Branch*)**: `analytics-report`
* **Daftar Tugas (TODO)**:
  - [ ] Mengintegrasikan library **PhpSpreadsheet** di backend PHP untuk ekspor data kehadiran langsung ke format `.xlsx`.
  - [ ] Membuat fitur unduh laporan rekap bulanan per siswa ke format **PDF** resmi.
  - [ ] Membuat filter pencarian cepat berdasarkan rentang tanggal dan nama kelas sebelum mengekspor data.

---

### 🧪 FITUR 6: Penjaminan Mutu & Uji Ketahanan (QA & Rilis)
Memastikan seluruh fitur stabil, responsif, bebas error, serta didukung panduan penggunaan yang lengkap.
* **Penanggung Jawab (PIC)**: **[Anggota 6] (QA Tester)** & **Aldi Januar Saputra (PM)**
* **Cabang Git (*Branch*)**: `qa-testing` & `main`
* **Daftar Tugas (TODO)**:
  - [ ] Melakukan uji coba ketahanan pemindai wajah pada berbagai perangkat (HP RAM rendah, laptop, kamera USB luar).
  - [ ] Menguji performa API Supabase saat beberapa user melakukan absen secara bersamaan.
  - [ ] Menyusun dokumen dokumentasi teknis (API Spec) & Buku Panduan Pengguna (User Manual).
  - [ ] **Project Manager**: Melakukan review kode pada seluruh branch pengembang tim, lalu menyatukannya ke cabang `main` setelah dinyatakan bebas bug.

---

## 🤝 PROTOKOL KOLABORASI GIT TIM (PM RULES)

1. **Dilarang keras melakukan Push langsung ke branch `main`!** Cabang `main` dilindungi dan hanya boleh di-update oleh Project Manager melalui integrasi kode.
2. **Alur Kerja**:
   - Anggota membuat cabang kerja dari branch `main` sesuai fitur yang dipegang: `git checkout -b <nama-cabang-fitur>`.
   - Setelah fitur selesai diuji di komputer masing-masing, dorong ke GitHub: `git push origin <nama-cabang-fitur>`.
3. **Pemeriksaan PM (Aldi)**:
   - Ambil update dari GitHub: `git fetch origin`.
   - Pindah dan uji cabang teman Anda secara lokal: `git checkout <nama-cabang-fitur>`.
   - Jika sudah oke, kembali ke `main`, gabungkan, dan dorong:
     ```powershell
     git checkout main
     git merge origin/<nama-cabang-fitur>
     git push origin main
     ```

---
*Dibuat oleh Tim Pengembang Face Attendance ID dengan semangat kolaborasi.* ❤️
