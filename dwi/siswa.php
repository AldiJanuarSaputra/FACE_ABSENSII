<?php
session_start();

if (!isset($_SESSION['admin_user'])) {
    header("Location: ../desta/login.php");
    exit;
}

$admin = $_SESSION['admin_user'];
include "../config/koneksi.php";

$pesan = '';
$tipePesan = 'info';

// ── 1. Proses Hapus (Delete) ──────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $koneksi->prepare("DELETE FROM siswa WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $pesan = "Data siswa berhasil dihapus!";
        $tipePesan = "ok";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: " . $e->getMessage();
        $tipePesan = "err";
    }
}

// ── 2. Proses Edit (Update) ───────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id       = (int)$_POST['id'];
    $nama     = trim($_POST['nama']);
    $kelas_id = (int)$_POST['kelas_id'];
    $nis      = trim($_POST['nis']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!$nama || !$kelas_id || !$nis) {
        $pesan = "Semua kolom edit harus diisi!";
        $tipePesan = "err";
    } else {
        try {
            // Ambil nama kelas untuk kelestarian kolom text 'kelas' (jika masih diperlukan)
            $stmtK = $koneksi->prepare("SELECT nama_kelas FROM kelas WHERE id = :id");
            $stmtK->execute([':id' => $kelas_id]);
            $nama_kelas = $stmtK->fetchColumn() ?: '';

            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $koneksi->prepare("UPDATE siswa SET nis = :nis, nama = :nama, kelas = :kelas, kelas_id = :kelas_id, password = :password WHERE id = :id");
                $stmt->execute([
                    ':nis'      => $nis,
                    ':nama'     => $nama,
                    ':kelas'    => $nama_kelas,
                    ':kelas_id' => $kelas_id,
                    ':password' => $hash,
                    ':id'       => $id
                ]);
            } else {
                $stmt = $koneksi->prepare("UPDATE siswa SET nis = :nis, nama = :nama, kelas = :kelas, kelas_id = :kelas_id WHERE id = :id");
                $stmt->execute([
                    ':nis'      => $nis,
                    ':nama'     => $nama,
                    ':kelas'    => $nama_kelas,
                    ':kelas_id' => $kelas_id,
                    ':id'       => $id
                ]);
            }
            $pesan = "Data siswa (NIS: $nis) berhasil diperbarui!";
            $tipePesan = "ok";
        } catch (PDOException $e) {
            $pesan = "Gagal memperbarui data: " . $e->getMessage();
            $tipePesan = "err";
        }
    }
}

// ── 3. Proses Ambil Data & Filter (Read) ──────────────
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$listSiswa = [];
$listKelas = [];

try {
    // Ambil list semua kelas untuk dropdown
    $stmtK = $koneksi->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
    $listKelas = $stmtK->fetchAll(PDO::FETCH_ASSOC);

    if ($cari !== '') {
        $stmt = $koneksi->prepare("SELECT s.id, s.nis, s.nama, COALESCE(k.nama_kelas, s.kelas) as kelas, s.kelas_id, s.wajah FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.nama ILIKE :cari OR s.nis ILIKE :cari ORDER BY s.nama ASC");
        $stmt->execute([':cari' => "%$cari%"]);
    } else {
        $stmt = $koneksi->query("SELECT s.id, s.nis, s.nama, COALESCE(k.nama_kelas, s.kelas) as kelas, s.kelas_id, s.wajah FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nama ASC");
    }
    $listSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pesan = "Error memuat data: " . $e->getMessage();
    $tipePesan = "err";
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Siswa – Face Absensi</title>
<link rel="manifest" href="../manifest.json">
<meta name="theme-color" content="#6366f1">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    // Theme Initializer (mencegah kedipan putih/FOUC)
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('../sw.js')
          .then(reg => console.log('Service Worker registered!', reg.scope))
          .catch(err => console.log('Service Worker failed!', err));
      });
    }
</script>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root, html[data-theme="dark"] {
    --bg-dark: #07090e;
    --bg-gradient: radial-gradient(circle at top, #111425 0%, #07090e 100%);
    --card-bg: #111422; /* Solid dark obsidian card */
    --card-border: #374151; /* Outline komponen ultra-tegas dark slate */
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --primary-glow: rgba(99, 102, 241, 0.3);
    --secondary: #0ea5e9;
    --secondary-hover: #0284c7;
    --secondary-glow: rgba(14, 165, 233, 0.3);
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --sidebar-bg: #090a12;
    --sidebar-border: #1f2937; /* Pembatas sidebar ultra-tegas */
    --active-menu: rgba(99, 102, 241, 0.15);
    --input-bg: #090a12;
    --row-border: rgba(99, 102, 241, 0.15); /* Garis pembatas baris tabel ultra-jelas */
}

html[data-theme="light"] {
    --bg-dark: #f8fafc;
    --bg-gradient: radial-gradient(circle at top, #e0e7ff 0%, #f8fafc 100%);
    --card-bg: #ffffff;
    --card-border: #cbd5e1; /* Outline komponen terang yang tegas */
    --primary: #4f46e5;
    --primary-hover: #4338ca;
    --primary-glow: rgba(79, 70, 229, 0.15);
    --secondary: #0ea5e9;
    --secondary-hover: #0284c7;
    --secondary-glow: rgba(14, 165, 233, 0.15);
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --sidebar-bg: #ffffff;
    --sidebar-border: #cbd5e1;
    --active-menu: rgba(79, 70, 229, 0.08);
    --input-bg: #f1f5f9;
    --row-border: rgba(0, 0, 0, 0.05); /* Garis pembatas baris tabel terang */
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    background: var(--bg-gradient);
    color: var(--text-primary);
    display: flex;
    overflow-x: hidden;
}

/* Layout Wrapper */
.layout-wrapper {
    display: flex;
    width: 100%;
    min-height: 100vh;
}

/* Sidebar Styling */
.sidebar {
    width: 280px;
    background: var(--sidebar-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-right: 2px solid var(--sidebar-border);
    padding: 30px 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 100;
}

.brand-section {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 40px;
}
.brand-icon {
    font-size: 24px;
    color: var(--primary);
}
.brand-name {
    font-family: 'Outfit', sans-serif;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.brand-name span {
    color: var(--primary);
}

.menu-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.menu-item a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 12px;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
}

.menu-item a:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.05);
}

.menu-item.active a {
    color: var(--primary);
    background: var(--active-menu);
    font-weight: 700;
}

/* Bottom Bar - Theme Toggle */
.sidebar-footer {
    border-top: 1px solid var(--sidebar-border);
    padding-top: 20px;
    margin-top: 20px;
}

.theme-toggle-btn {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 2px solid var(--card-border);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.theme-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
}

/* Main Content Area */
.main-content {
    flex: 1;
    padding: 40px;
    overflow-y: auto;
    transition: all 0.3s ease;
}

/* Header Dashboard */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    border-bottom: 1px solid var(--card-border);
    padding-bottom: 20px;
}

.welcome-box h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 4px;
}
.welcome-box p {
    color: var(--text-secondary);
    font-size: 14px;
}

/* Hamburger Menu */
.hamburger {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-primary);
}

/* Notifikasi */
.alert {
    padding: 15px 20px;
    border-radius: 16px;
    margin-bottom: 25px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: fadeIn 0.4s ease-out;
}
.alert-ok { background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--success); }
.alert-err { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--danger); }
.alert-info { background: rgba(14, 165, 233, 0.12); border: 1px solid rgba(14, 165, 233, 0.2); color: var(--secondary); }

/* Filter Box */
.filter-wrap {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 2px solid var(--card-border);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
}

.filter-group label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    font-weight: 700;
}

.filter-group input {
    width: 100%;
    padding: 12px;
    background: rgba(255, 255, 255, 0.04);
    border: 2px solid var(--card-border);
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    outline: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.3s ease;
}

.filter-group input:focus {
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.08);
}

.btn-filter {
    padding: 12px 24px;
    background: var(--primary);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--primary-glow);
}
.btn-filter:hover { 
    background: var(--primary-hover);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35); 
}

.btn-reset {
    padding: 12px 18px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.3s ease;
}
.btn-reset:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
}

/* Table Section */
.table-container {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 2px solid var(--card-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

th {
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-secondary);
    padding: 16px 20px;
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--card-border);
}

td {
    padding: 16px 20px;
    font-size: 14px;
    border-bottom: 1px solid var(--row-border);
    vertical-align: middle;
}

tr:hover td {
    background: rgba(255, 255, 255, 0.01);
}

/* Avatar Wajah */
.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid var(--card-border);
    object-fit: cover;
    background: #000;
}

.avatar-none {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px dashed rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: var(--text-secondary);
    background: rgba(255, 255, 255, 0.02);
}

/* Action Buttons */
.btn-edit {
    padding: 8px 14px;
    background: rgba(14, 165, 233, 0.12);
    color: var(--secondary);
    border: 1px solid rgba(14, 165, 233, 0.2);
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.3s ease;
    margin-right: 5px;
}
.btn-edit:hover { background: rgba(14, 165, 233, 0.2); transform: translateY(-1px); }

.btn-delete {
    padding: 8px 14px;
    background: rgba(239, 68, 68, 0.12);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.3s ease;
}
.btn-delete:hover { background: rgba(239, 68, 68, 0.2); transform: translateY(-1px); }

/* Modal Edit */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(9, 15, 29, 0.8);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: var(--bg-dark);
    border: 1px solid var(--card-border);
    width: 100%;
    max-width: 400px;
    border-radius: 24px;
    padding: 25px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
    animation: zoomIn 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.modal-header h3 { 
    font-family: 'Outfit', sans-serif;
    font-size: 20px; 
    font-weight: 800;
    color: var(--text-primary); 
}
.btn-close {
    background: none; border: none; color: var(--text-secondary);
    font-size: 20px; cursor: pointer; transition: 0.2s;
}
.btn-close:hover { color: var(--text-primary); }

.modal-body label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 6px;
    margin-top: 12px;
    font-weight: 700;
}
.modal-body input {
    width: 100%;
    padding: 11px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--card-border);
    border-radius: 10px;
    color: #fff;
    outline: none;
    font-size: 14px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.3s ease;
}

.modal-body input:focus {
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.08);
}

.btn-save {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    background: var(--primary);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--primary-glow);
}
.btn-save:hover { 
    background: var(--primary-hover);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35); 
    transform: translateY(-1px); 
}

@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

/* Responsive Styles */
@media (max-width: 992px) {
    .sidebar {
        position: fixed;
        left: -280px;
        top: 0; bottom: 0;
        box-shadow: 25px 0 50px rgba(0,0,0,0.4);
    }
    .sidebar.active {
        left: 0;
    }
    .main-content {
        padding: 30px 20px;
    }
    .hamburger {
        display: block;
    }
}
</style>
</head>
<body>

<div class="layout-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div>
            <div class="brand-section">
                <i class="fa-solid fa-face-viewfinder brand-icon"></i>
                <h1 class="brand-name">Face<span>Absen</span></h1>
            </div>
            
            <ul class="menu-list">
                <li class="menu-item">
                    <a href="../veve/index.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                </li>
                <li class="menu-item">
                    <a href="../dwi/kelas.php"><i class="fa-solid fa-school"></i>Manajemen Kelas</a>
                </li>
                <li class="menu-item active">
                    <a href="../dwi/siswa.php"><i class="fa-solid fa-users-gear"></i>Kelola Siswa</a>
                </li>
                <li class="menu-item">
                    <a href="../hasbi/rekap.php"><i class="fa-solid fa-chart-line"></i>Rekap Absensi</a>
                </li>
                <li class="menu-item">
                    <a href="../aldi/database_console.php" style="color: #3ecf8e;"><i class="fa-solid fa-database"></i>Konsol Database</a>
                </li>
                <li class="menu-item" style="margin-top: 15px; border-top: 1px solid var(--sidebar-border); padding-top: 15px;">
                    <a href="../fiis/absensi.php" style="color: var(--primary);"><i class="fa-solid fa-camera"></i>Scan Kehadiran</a>
                </li>
                <li class="menu-item">
                    <a href="../fiis/register.php" style="color: var(--secondary);"><i class="fa-solid fa-user-plus"></i>Registrasi Wajah</a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer" style="display: flex; flex-direction: column; gap: 10px;">
            <button class="theme-toggle-btn" onclick="toggleTheme()" id="themeBtn">
                <i class="fa-solid fa-moon"></i>
                <span id="themeBtnText">Mode Terang</span>
            </button>
            <a href="../desta/logout.php" class="theme-toggle-btn" style="border-color: rgba(239, 68, 68, 0.15); color: var(--danger); background: rgba(239, 68, 68, 0.05); text-decoration: none;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="welcome-box">
                <h2>Kelola Data Siswa</h2>
                <p>Cari, edit, atau hapus pendaftaran wajah siswa.</p>
            </div>
            <div class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </div>
        </header>

        <!-- Notifikasi -->
        <?php if ($pesan !== ''): ?>
            <div class="alert alert-<?php echo $tipePesan; ?>">
                <i class="fa-solid <?php 
                    echo $tipePesan === 'ok' ? 'fa-circle-check' : ($tipePesan === 'err' ? 'fa-circle-exclamation' : 'fa-circle-info'); 
                ?>"></i>
                <span><?php echo htmlspecialchars($pesan); ?></span>
            </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="filter-wrap">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="cari">Cari Nama / NIS Siswa</label>
                    <input type="text" id="cari" name="cari" placeholder="Ketik nama atau NIS siswa..." value="<?php echo htmlspecialchars($cari); ?>">
                </div>
                <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i>Cari</button>
                <?php if ($cari !== ''): ?>
                    <a href="../dwi/siswa.php" class="btn-reset" style="padding: 12px 18px;"><i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i>Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table Siswa -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">Foto</th>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th style="width: 200px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($listSiswa) > 0): ?>
                        <?php foreach ($listSiswa as $row): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if (!empty($row['wajah'])): ?>
                                        <img src="<?php echo $row['wajah']; ?>" alt="Foto Wajah" class="avatar">
                                    <?php else: ?>
                                        <div class="avatar-none"><i class="fa-solid fa-user"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 700; color: var(--secondary);"><?php echo htmlspecialchars($row['nis']); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><span style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); padding: 5px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--text-secondary);"><?php echo htmlspecialchars($row['kelas']); ?></span></td>
                                <td style="text-align: center; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; border-bottom: none;">
                                    <button class="btn-edit" style="padding: 6px 12px; margin: 0; display: inline-flex; align-items: center;" onclick="bukaModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nis']); ?>', '<?php echo addslashes($row['nama']); ?>', <?php echo (int)$row['kelas_id']; ?>)">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i>Edit
                                    </button>
                                    <button class="btn-delete" style="padding: 6px 12px; margin: 0; display: inline-flex; align-items: center;" onclick="konfirmasiHapus(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama']); ?>')">
                                        <i class="fa-solid fa-trash" style="margin-right: 5px;"></i>Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state" style="padding: 50px; text-align: center; color: var(--text-secondary);">
                                <i class="fa-solid fa-users-slash" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                Belum ada siswa terdaftar atau pencarian tidak ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Edit Siswa -->
<div id="modalEdit" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-pen" style="margin-right: 8px;"></i>Edit Data Siswa</h3>
            <button class="btn-close" onclick="tutupModal()">&times;</button>
        </div>
        <form method="POST" action="siswa.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-id" name="id">
            
            <div class="modal-body">
                <label for="edit-nis">NIS Siswa</label>
                <input type="text" id="edit-nis" name="nis" required>

                <label for="edit-nama">Nama Lengkap</label>
                <input type="text" id="edit-nama" name="nama" required>
                <label for="edit-kelas">Kelas</label>
                <select id="edit-kelas" name="kelas_id" required style="width: 100%; padding: 11px; background: rgba(255, 255, 255, 0.04); border: 1px solid var(--card-border); border-radius: 10px; color: #fff; outline: none; font-size: 14px; font-family: 'Plus Jakarta Sans', sans-serif;">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="edit-password" style="display: block; font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; margin-top: 12px; font-weight: 700;">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" id="edit-password" name="password" placeholder="Ketik password baru siswa..." style="width: 100%; padding: 11px; background: rgba(255, 255, 255, 0.04); border: 1px solid var(--card-border); border-radius: 10px; color: #fff; outline: none; font-size: 14px; font-family: 'Plus Jakarta Sans', sans-serif;">
            </div>

            <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i>Simpan Perubahan</button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById("modalEdit");

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    updateThemeUI(newTheme);
}

function updateThemeUI(theme) {
    const themeBtn = document.getElementById('themeBtn');
    
    if (theme === 'light') {
        themeBtn.innerHTML = '<i class="fa-solid fa-sun" style="color: #f59e0b;"></i><span id="themeBtnText">Mode Gelap</span>';
    } else {
        themeBtn.innerHTML = '<i class="fa-solid fa-moon"></i><span id="themeBtnText">Mode Terang</span>';
    }
}

// Set correct toggle button UI on page load
updateThemeUI(savedTheme);

function bukaModal(id, nis, nama, kelasId) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-nis").value = nis;
    document.getElementById("edit-nama").value = nama;
    document.getElementById("edit-kelas").value = kelasId;
    modal.style.display = "flex";
}

function tutupModal() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        tutupModal();
    }
}

function konfirmasiHapus(id, nama) {
    if (confirm("Apakah Anda yakin ingin menghapus data siswa '" + nama + "'? Semua riwayat absensi siswa ini juga sebaiknya diperiksa.")) {
        window.location.href = "siswa.php?action=delete&id=" + id;
    }
}
</script>
</body>
</html>
