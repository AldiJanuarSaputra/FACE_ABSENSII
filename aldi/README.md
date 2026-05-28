# 📁 Folder: `aldi/` — Tugas Aldi Januar Saputra

## 👤 Anggota
**Aldi Januar Saputra** — Database & API Security

## 📋 Deskripsi Tugas
Mengelola integrasi database **Supabase PostgreSQL** secara aman, memastikan koneksi berjalan lancar, dan mengelola variabel lingkungan melalui file `.env`.

## 📂 File di Folder Ini
| File | Deskripsi |
|------|-----------|
| [`database_console.php`](./database_console.php) | Halaman Konsol Database Supabase — monitor koneksi, tabel statistik, dan jalankan migrasi |

## ✅ Checklist Tugas
- [ ] Pastikan file `config/koneksi.php` terintegrasi benar dengan Supabase
- [ ] Kelola variabel kredensial database di `.env` agar tidak ter-upload ke repositori publik
- [ ] Gunakan `config/koneksi.php` dengan PDO untuk query aman
- [ ] Lakukan migrasi skema database (tambah kolom, buat tabel) via konsol

## 🔗 File Terkait (di luar folder)
- `config/koneksi.php` — File koneksi database (jangan edit langsung, koordinasi dengan tim)
- `.env` — Variabel lingkungan (JANGAN commit ke git!)
- `.gitignore` — Pastikan `.env` sudah ada di sini

## 🚀 Cara Akses
```
http://localhost/Face_absensi/aldi/database_console.php
```
