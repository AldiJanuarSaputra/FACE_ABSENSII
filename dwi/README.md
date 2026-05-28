# 📁 Folder: `dwi/` — Tugas Dwi

## 👤 Anggota
**Dwi** — CRUD Siswa & Kelas

## 📋 Deskripsi Tugas
Mengelola fitur **CRUD (Create, Read, Update, Delete)** untuk data siswa dan kelas. Bertanggung jawab atas halaman manajemen kelas dan pengelolaan daftar siswa per kelas.

## 📂 File di Folder Ini
| File | Deskripsi |
|------|-----------|
| [`kelas.php`](./kelas.php) | Manajemen data kelas — tambah, edit, hapus kelas |
| [`kelas_detail.php`](./kelas_detail.php) | Detail kelas — daftar siswa per kelas, kelola anggota |
| [`siswa.php`](./siswa.php) | Pengelolaan siswa global — cari, edit, hapus siswa |

## ✅ Checklist Tugas
- [ ] Tampilkan daftar semua kelas dengan fitur tambah/edit/hapus
- [ ] Halaman detail kelas dengan daftar siswa yang terdaftar
- [ ] Fitur pencarian dan filter siswa
- [ ] Operasi CRUD siswa (tambah, edit, hapus) dengan konfirmasi
- [ ] Validasi data input (NIS unik, nama tidak kosong, dll.)

## 🔗 File Terkait (di luar folder)
- `config/koneksi.php` — Koneksi database (koordinasi dengan Aldi)
- `api/` — Endpoint API jika menggunakan AJAX

## 🚀 Cara Akses
```
http://localhost/Face_absensi/dwi/kelas.php
http://localhost/Face_absensi/dwi/siswa.php
http://localhost/Face_absensi/dwi/kelas_detail.php?id=1
```
