# 📖 Panduan Pemula: Cara Menggunakan Git & GitHub untuk Kolaborasi Tim
## 📸 Proyek: Sistem Absensi Face ID (Kelompok Aldi, Fiis, Veve, Hasbi, Dwi, Desta)

Halo Tim! Selamat datang di panduan pemula Git & GitHub. Jika ini pertama kalinya kalian menggunakan Git, jangan khawatir! Panduan ini dirancang khusus agar sangat mudah dipahami, lengkap dengan analogi sederhana dan perintah yang siap kalian gunakan.

---

## 💡 1. APA ITU GIT & GITHUB? (ANALOGI SEDERHANA)

* **Git (Time Machine)**: Bayangkan Git adalah sebuah mesin waktu untuk kode program kita. Setiap kali kita selesai membuat fitur baru, kita bisa membuat "titik simpan" (*Save Point*). Jika di kemudian hari kode kita error atau rusak, kita bisa kembali ke titik simpan sebelumnya dengan mudah.
* **GitHub (Cloud Collaboration Space)**: Jika Git berjalan di komputer masing-masing secara offline, maka GitHub adalah "Google Drive" versi canggih tempat kita menyimpan proyek kita di internet secara online agar bisa diakses dan dikerjakan bersama-sama oleh tim.

---

## 🛠️ 2. SETUP AWAL (HANYA DILAKUKAN SEKALI)

Sebelum mulai bekerja, pastikan Git sudah terinstal di komputer kalian. Jika belum, download di [git-scm.com](https://git-scm.com/) lalu instal.

Setelah terinstal, buka **VS Code** ➡️ buka terminal kalian (tekan tombol `Ctrl + ~`), lalu jalankan perintah berikut untuk memperkenalkan diri ke Git:

```powershell
# 1. Daftarkan nama lengkap kalian
git config --global user.name "Nama Lengkap Anda"

# 2. Daftarkan email yang terhubung ke akun GitHub kalian
git config --global user.email "emailanda@gmail.com"
```
*(Ganti tulisan di dalam tanda kutip sesuai dengan nama dan email kalian masing-masing!)*

---

## 🌿 3. PANDUAN KERJA UNTUK ANGGOTA TIM (FIIS, VEVE, HASBI, DWI, DESTA)

Sebagai anggota tim pengembang, ikuti langkah-langkah di bawah ini setiap kali kalian ingin mulai membuat atau memperbarui fitur.

### Langkah A: Mengunduh Proyek ke Komputer Kalian (Pertama Kali Saja)
Jika folder proyek belum ada sama sekali di komputer kalian, unduh repositori dari GitHub dengan mengetikkan ini di terminal VS Code:
```powershell
git clone https://github.com/AldiJanuarSaputra/FACE_ABSENSII.git
```
*(Setelah selesai, buka folder `Face_absensi` tersebut di VS Code kalian).*

---

### Langkah B: Masuk ke Cabang Kerja (*Branch*) Kalian
Setiap orang sudah dibuatkan cabang khusus agar tidak mengganggu pekerjaan satu sama lain. Sebelum mulai mengedit file, masuklah ke cabang kalian masing-masing:

```powershell
# 1. Update daftar cabang terbaru dari internet
git fetch origin

# 2. Berpindah ke cabang kalian masing-masing
git checkout <nama-cabang-kalian>
```
**Contoh**:
* Veve mengetik: `git checkout veve`
* Hasbi mengetik: `git checkout hasbi`
* Fiis mengetik: `git checkout fiis`
* Dwi mengetik: `git checkout dwi`
* Desta mengetik: `git checkout desta`

*(Ketik `git status` untuk memastikan kalian sudah berada di cabang yang benar).*

---

### Langkah C: Mulai Mengoding / Mengedit File
Silakan buka file PHP/CSS/JS kalian di VS Code dan mulailah membuat fitur yang ditugaskan kepada kalian.

---

### Langkah D: Menyimpan Pekerjaan Kalian (Commit)
Setelah fitur selesai dibuat atau kalian ingin beristirahat, simpan pekerjaan kalian ke dalam mesin waktu Git lokal:

```powershell
# 1. Cek file apa saja yang sudah kalian edit
git status

# 2. Siapkan semua file yang diubah untuk disimpan
git add .

# 3. Simpan perubahan dengan memberikan pesan penjelasan yang singkat & jelas
git commit -m "Menyelesaikan fitur login admin"
```

---

### Langkah E: Mengirim Hasil Kerja ke GitHub (Push)
Kirim "titik simpan" (*save point*) dari komputer kalian ke GitHub di internet agar bisa dilihat oleh PM (Aldi):

```powershell
git push origin <nama-cabang-kalian>
```
**Contoh**:
* Veve mengetik: `git push origin veve`
* Hasbi mengetik: `git push origin hasbi`

> [!IMPORTANT]
> **Autentikasi Browser (Pertama Kali Push)**:
> Saat pertama kali mengetik `git push`, akan muncul pop-up dari **Git Credential Manager**. 
> 1. Klik **"Sign in with your browser"**.
> 2. Browser Chrome akan terbuka otomatis. 
> 3. Klik tombol hijau **"Authorize GitCredentialManager"**.
> 4. Selesai! Git kalian sudah terhubung secara otomatis ke GitHub.

---

### Langkah F: Mengambil Update Terbaru dari Cabang Utama (`main`)
Terkadang, Aldi (PM) akan menggabungkan fitur yang sudah selesai ke cabang utama (`main`). Agar kode kalian tidak kedaluwarsa, ambil update terbaru dari `main` secara rutin dengan cara:

```powershell
# Ambil update terbaru dari main dan gabungkan ke cabang kerja kalian
git pull origin main
```

---

## 👑 4. PANDUAN KERJA UNTUK PROJECT MANAGER (ALDI)

Sebagai PM, tugas utama Anda adalah menguji dan menggabungkan kerja teman-teman Anda dari cabang mereka ke cabang utama `main`.

### Cara Menggabungkan (*Merge*) Fitur Tim:

```powershell
# 1. Ambil seluruh update terbaru dari GitHub
git fetch origin

# 2. Berpindah ke cabang teman Anda (misal veve) untuk meninjau kodenya di VS Code Anda (opsional)
git checkout veve

# 3. Berpindah kembali ke cabang main utama
git checkout main

# 4. Gabungkan kerjaan cabang veve ke cabang main
git merge origin/veve

# 5. Dorong cabang main yang sudah terupdate ke GitHub agar bisa diakses tim lain
git push origin main
```

---

## 🚨 5. ATURAN EMAS KOLABORASI (GOLDEN RULES)

1. **JANGAN PERNAH** melakukan push langsung ke cabang `main` jika kalian bukan PM. Selalu bekerja di cabang kalian masing-masing (`veve`, `hasbi`, `fiis`, dst).
2. **Selalu Pull Sebelum Mulai**: Sebelum mulai mengoding di hari baru, biasakan jalankan `git pull origin main` agar kode kalian selalu sinkron dengan versi terbaru.
3. **Pesan Commit yang Jelas**: Tulis pesan commit yang menerangkan apa yang kalian lakukan (Contoh yang baik: `git commit -m "Memperbaiki UI kamera di HP"`, jangan hanya menulis `git commit -m "tes"`).

---
*Semangat belajar Git semuanya! Jika ada kendala atau error di terminal, segera hubungi Aldi sebagai Project Manager.* 🚀
