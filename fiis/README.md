# 📁 Folder: `fiis/` — Tugas Fiis

## 👤 Anggota
**Fiis** — Face Recognition System

## 📋 Deskripsi Tugas
Mengembangkan fitur utama **pengenalan wajah** berbasis AI untuk absensi real-time dan pendaftaran wajah baru siswa menggunakan kamera.

## 📂 File di Folder Ini
| File | Deskripsi |
|------|-----------|
| [`absensi.php`](./absensi.php) | Halaman deteksi & absensi wajah real-time via kamera |
| [`register.php`](./register.php) | Pendaftaran dan perekaman data wajah siswa baru |
| [`tescamera.php`](./tescamera.php) | Halaman tes kamera sebelum digunakan |

## ✅ Checklist Tugas
- [ ] Integrasi library face-api.js untuk deteksi wajah
- [ ] Halaman absensi kamera yang berjalan otomatis tanpa klik
- [ ] Fitur registrasi wajah siswa dengan validasi NIS
- [ ] Simpan data absensi ke database dengan timestamp
- [ ] Halaman tes kamera untuk debugging hardware

## 🔗 File Terkait (di luar folder)
- `models/` — File model AI face-api.js (jangan hapus!)
- `js/` — File JavaScript pendukung
- `api/` — Endpoint AJAX untuk simpan absensi dan wajah
- `config/koneksi.php` — Koneksi database (koordinasi dengan Aldi)

## 🚀 Cara Akses
```
http://localhost/Face_absensi/fiis/absensi.php
http://localhost/Face_absensi/fiis/register.php
http://localhost/Face_absensi/fiis/tescamera.php
```

> ⚠️ **Penting**: Pastikan akses kamera diizinkan di browser dan koneksi HTTPS/localhost tersedia agar face-api.js dapat berjalan.
