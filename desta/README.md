# 📁 Folder: `desta/` — Tugas Desta

## 👤 Anggota
**Desta** — Authentication System

## 📋 Deskripsi Tugas
Membangun sistem **login & logout** yang aman untuk admin dan siswa, serta halaman registrasi admin baru.

## 📂 File di Folder Ini
| File | Deskripsi |
|------|-----------|
| [`login.php`](./login.php) | Halaman form login — mendukung role admin & siswa |
| [`logout.php`](./logout.php) | Script proses logout & hapus sesi |
| [`register_admin.php`](./register_admin.php) | Halaman pendaftaran akun admin baru |

## ✅ Checklist Tugas
- [ ] Form login dengan validasi input dan pesan error yang jelas
- [ ] Penanganan session admin dan siswa secara terpisah
- [ ] Script logout yang aman (destroy session, redirect ke login)
- [ ] Form registrasi admin baru dengan validasi password
- [ ] Proteksi halaman dengan session check di setiap halaman admin

## 🔗 File Terkait (di luar folder)
- `config/koneksi.php` — Koneksi database (koordinasi dengan Aldi)

## 🚀 Cara Akses
```
http://localhost/Face_absensi/desta/login.php
http://localhost/Face_absensi/desta/register_admin.php
```
