<?php
session_start();

if (!isset($_SESSION['admin_user'])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['admin_user'];
include "koneksi.php";
try {
    $qSiswa = $koneksi->query("SELECT COUNT(*) FROM siswa");
    $totalSiswa = $qSiswa->fetchColumn();

    // Query untuk absensi hari ini (tanggal sekarang)
    $hariIni = date('Y-m-d');
    $qHadir = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = :tgl AND status = 'Hadir'");
    $qHadir->execute([':tgl' => $hariIni]);
    $totalHadir = $qHadir->fetchColumn();

    $qKelas = $koneksi->query("SELECT COUNT(*) FROM kelas");
    $totalKelas = $qKelas->fetchColumn();

    $qLambat = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = :tgl AND status = 'Terlambat'");
    $qLambat->execute([':tgl' => $hariIni]);
    $totalLambat = $qLambat->fetchColumn();
} catch (PDOException $e) {
    $totalSiswa = 0;
    $totalKelas = 0;
    $totalHadir = 0;
    $totalLambat = 0;
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Face Absensi</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#6366f1">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    // Theme Initializer (mencegah kedipan putih/FOUC)
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
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
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --sidebar-bg: #090a12;
    --sidebar-border: #1f2937; /* Pembatas sidebar ultra-tegas */
    --active-menu: rgba(99, 102, 241, 0.15);
    --input-bg: #090a12;
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
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --sidebar-bg: #ffffff;
    --sidebar-border: #cbd5e1;
    --active-menu: rgba(79, 70, 229, 0.08);
    --input-bg: #f1f5f9;
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

/* Grid Statistik */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 2px solid var(--card-border);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-icon.blue { background: rgba(14, 165, 233, 0.12); color: var(--secondary); border: 1px solid rgba(14, 165, 233, 0.2); }
.stat-icon.green { background: rgba(16, 185, 129, 0.12); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
.stat-icon.red { background: rgba(239, 68, 68, 0.12); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }

.stat-info h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    margin-bottom: 4px;
}
.stat-info p {
    font-family: 'Outfit', sans-serif;
    font-size: 30px;
    font-weight: 800;
}

/* Immersive Action Cards */
.action-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 35px;
}

.action-card {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    border: 2px solid var(--card-border);
    border-radius: 24px;
    padding: 30px;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
}

.action-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: var(--primary);
    opacity: 0.8;
}

.action-card.sec-accent::before {
    background: var(--secondary);
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.action-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}
.action-card-header i {
    font-size: 28px;
}
.action-card-header h4 {
    font-family: 'Outfit', sans-serif;
    font-size: 20px;
    font-weight: 800;
}

.action-card p {
    color: var(--text-secondary);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 25px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    background: var(--primary);
    box-shadow: 0 4px 15px var(--primary-glow);
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: var(--primary-hover);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

.action-card.sec-accent .action-btn {
    background: var(--secondary);
    box-shadow: 0 4px 15px var(--secondary-glow);
}
.action-card.sec-accent .action-btn:hover {
    background: var(--secondary-hover);
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);
}

/* Info Banner */
.quick-guide {
    background: var(--card-bg);
    border: 2px solid var(--card-border);
    border-radius: 20px;
    padding: 24px;
}

.quick-guide h4 {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.quick-guide ul {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.quick-guide li {
    font-size: 13.5px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 8px;
}
.quick-guide li i {
    color: var(--primary);
    font-size: 14px;
}

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
                <li class="menu-item active">
                    <a href="index.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                </li>
                <li class="menu-item">
                    <a href="kelas.php"><i class="fa-solid fa-school"></i>Manajemen Kelas</a>
                </li>
                <li class="menu-item">
                    <a href="siswa.php"><i class="fa-solid fa-users-gear"></i>Kelola Siswa</a>
                </li>
                <li class="menu-item">
                    <a href="rekap.php"><i class="fa-solid fa-chart-line"></i>Rekap Absensi</a>
                </li>
                <li class="menu-item" style="margin-top: 15px; border-top: 1px solid var(--sidebar-border); padding-top: 15px;">
                    <a href="absensi.php" style="color: var(--primary);"><i class="fa-solid fa-camera"></i>Scan Kehadiran</a>
                </li>
                <li class="menu-item">
                    <a href="register.php" style="color: var(--secondary);"><i class="fa-solid fa-user-plus"></i>Registrasi Wajah</a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer" style="display: flex; flex-direction: column; gap: 10px;">
            <button class="theme-toggle-btn" onclick="toggleTheme()" id="themeBtn">
                <i class="fa-solid fa-moon"></i>
                <span id="themeBtnText">Mode Terang</span>
            </button>
            <a href="logout.php" class="theme-toggle-btn" style="border-color: rgba(239, 68, 68, 0.15); color: var(--danger); background: rgba(239, 68, 68, 0.05); text-decoration: none;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Dashboard Area -->
    <main class="main-content">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="welcome-box">
                <h2>Dasbor Admin</h2>
                <p>Selamat datang di sistem manajemen absensi cerdas.</p>
            </div>
            <div class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <h3>Total Siswa Terdaftar</h3>
                    <p><?php echo $totalSiswa; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.12); color: var(--primary);"><i class="fa-solid fa-school"></i></div>
                <div class="stat-info">
                    <h3>Total Kelas Aktif</h3>
                    <p><?php echo $totalKelas; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <h3>Hadir Hari Ini</h3>
                    <p><?php echo $totalHadir; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="stat-info">
                    <h3>Terlambat Hari Ini</h3>
                    <p><?php echo $totalLambat; ?></p>
                </div>
            </div>
        </section>

        <!-- Immersive Action Cards -->
        <section class="action-section">
            <div class="action-card">
                <div class="action-card-header">
                    <i class="fa-solid fa-camera" style="color: var(--primary);"></i>
                    <h4>Pintu Pemindaian Wajah</h4>
                </div>
                <p>Buka konsol pemindaian kehadiran utama sekolah. Letakkan tablet/HP di dekat pintu masuk untuk mencatat kehadiran siswa secara mandiri menggunakan kamera depan.</p>
                <a href="absensi.php" class="action-btn">Buka Absensi Kamera</a>
            </div>

            <div class="action-card sec-accent">
                <div class="action-card-header">
                    <i class="fa-solid fa-user-plus" style="color: var(--secondary);"></i>
                    <h4>Pendaftaran Wajah Baru</h4>
                </div>
                <p>Tambahkan siswa baru ke dalam sistem dan rekam data wajah unik mereka menggunakan webcam laptop/tablet admin untuk pendaftaran absensi offline.</p>
                <a href="register.php" class="action-btn">Mulai Pendaftaran</a>
            </div>
        </section>

        <!-- Quick Guide -->
        <footer class="quick-guide">
            <h4><i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Instruksi Cepat Dasbor</h4>
            <ul>
                <li><i class="fa-solid fa-chevron-right"></i> Gunakan tab **Manajemen Kelas** untuk mendaftarkan kelas dan menambahkan siswa ke dalamnya.</li>
                <li><i class="fa-solid fa-chevron-right"></i> Gunakan tab **Kelola Siswa** untuk mencari, mengedit, atau menghapus daftar siswa terdaftar secara global.</li>
                <li><i class="fa-solid fa-chevron-right"></i> Buka tab **Rekap Absensi** untuk menyaring laporan kehadiran harian/bulanan berdasarkan kelas dan mengunduh berkas laporan cetak resmi.</li>
                <li><i class="fa-solid fa-chevron-right"></i> Anda dapat mengganti tema sistem (Terang / Gelap) dari pojok bawah menu Sidebar kiri kapan saja.</li>
            </ul>
        </footer>
    </main>
</div>

<script>
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
</script>
</body>
</html>