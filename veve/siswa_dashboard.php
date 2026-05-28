<?php
session_start();
include "../config/koneksi.php";

// Middleware: Hanya boleh diakses oleh siswa yang sudah login
if (!isset($_SESSION['siswa_user'])) {
    header("Location: ../desta/login.php");
    exit;
}

$siswa = $_SESSION['siswa_user'];
$riwayat = [];
$totalHadir = 0;
$totalLambat = 0;

try {
    // 1. Ambil riwayat absensi pribadi
    // Gunakan nama dan kelas sebagai pencocok data absensi (atau bisa juga NIS jika ada di tabel absensi)
    // Di file absensi.php, data disimpan menggunakan {nis, nama, kelas}
    // Tapi karena tabel absensi memiliki nama dan kelas, kita query mencocokkan keduanya.
    // Jika tabel absensi memiliki kolom nis, kita query berdasarkan nis. Mari kita coba query berdasarkan nama & kelas
    $stmt = $koneksi->prepare("SELECT tanggal, jam, status FROM absensi WHERE nama = :nama AND kelas = :kelas ORDER BY tanggal DESC, jam DESC");
    $stmt->execute([
        ':nama'  => $siswa['nama'],
        ':kelas' => $siswa['kelas']
    ]);
    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Hitung statistik kehadiran personal
    foreach ($riwayat as $row) {
        if ($row['status'] === 'Hadir') {
            $totalHadir++;
        } elseif ($row['status'] === 'Terlambat') {
            $totalLambat++;
        }
    }
} catch (PDOException $e) {
    // Error silent handling
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Siswa – Face Absensi</title>
<link rel="manifest" href="../manifest.json">
<meta name="theme-color" content="#6366f1">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
</script>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root, html[data-theme="dark"] {
    --bg-dark: #090f1d;
    --bg-gradient: radial-gradient(circle at top, #1e1b4b 0%, #090f1d 100%);
    --card-bg: rgba(15, 23, 42, 0.55);
    --card-border: rgba(255, 255, 255, 0.06);
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --primary-glow: rgba(99, 102, 241, 0.2);
    --secondary: #0ea5e9;
    --secondary-hover: #0284c7;
    --secondary-glow: rgba(14, 165, 233, 0.2);
    --success: #10b981;
    --danger: #ef4444;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --sidebar-bg: rgba(15, 23, 42, 0.4);
    --sidebar-border: rgba(255, 255, 255, 0.05);
    --active-menu: rgba(99, 102, 241, 0.15);
}

html[data-theme="light"] {
    --bg-dark: #f8fafc;
    --bg-gradient: radial-gradient(circle at top, #e0e7ff 0%, #f8fafc 100%);
    --card-bg: rgba(255, 255, 255, 0.8);
    --card-border: rgba(99, 102, 241, 0.08);
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
    --sidebar-bg: rgba(255, 255, 255, 0.65);
    --sidebar-border: rgba(99, 102, 241, 0.08);
    --active-menu: rgba(79, 70, 229, 0.08);
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    background: var(--bg-gradient);
    color: var(--text-primary);
    display: flex;
    overflow-x: hidden;
}

.layout-wrapper {
    display: flex;
    width: 100%;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 280px;
    background: var(--sidebar-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-right: 1px solid var(--sidebar-border);
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

.sidebar-footer {
    border-top: 1px solid var(--sidebar-border);
    padding-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-sidebar-action {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--card-border);
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}
.btn-sidebar-action:hover {
    background: rgba(255, 255, 255, 0.08);
}
.btn-logout {
    border-color: rgba(239, 68, 68, 0.15) !important;
    color: var(--danger) !important;
    background: rgba(239, 68, 68, 0.05) !important;
}
.btn-logout:hover {
    background: rgba(239, 68, 68, 0.12) !important;
}

/* Main Content */
.main-content {
    flex: 1;
    padding: 40px;
    overflow-y: auto;
}

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

.hamburger {
    display: none;
    font-size: 24px;
    cursor: pointer;
}

/* Info Cards Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.info-card {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.info-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.info-icon.indigo { background: rgba(99, 102, 241, 0.12); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.2); }
.info-icon.blue { background: rgba(14, 165, 233, 0.12); color: var(--secondary); border: 1px solid rgba(14, 165, 233, 0.2); }
.info-icon.green { background: rgba(16, 185, 129, 0.12); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
.info-icon.red { background: rgba(239, 68, 68, 0.12); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }

.info-text h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    margin-bottom: 4px;
}
.info-text p {
    font-family: 'Outfit', sans-serif;
    font-size: 24px;
    font-weight: 800;
}

/* Quick Action Row */
.quick-action-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 35px;
}

.action-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 24px;
    position: relative;
    overflow: hidden;
}
.action-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: var(--primary);
}
.action-card.sec-accent::before {
    background: var(--secondary);
}

.action-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.action-header i { font-size: 22px; color: var(--primary); }
.action-card.sec-accent .action-header i { color: var(--secondary); }
.action-header h4 { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; }

.action-card p { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px; }

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    background: var(--primary);
    text-decoration: none;
    transition: 0.2s;
}
.action-btn:hover { background: var(--primary-hover); }
.action-card.sec-accent .action-btn { background: var(--secondary); }
.action-card.sec-accent .action-btn:hover { background: var(--secondary-hover); }

/* Table Container */
.table-container {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
}

.table-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--card-border);
}
.table-header h3 { font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; }

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: rgba(255, 255, 255, 0.02);
    color: var(--text-secondary);
    padding: 14px 20px;
    font-family: 'Outfit', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--card-border);
}

td {
    padding: 14px 20px;
    font-size: 13.5px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-hadir {
    background: rgba(16, 185, 129, 0.12);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.badge-lambat {
    background: rgba(239, 68, 68, 0.12);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

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
    .hamburger { display: block; }
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
                <h1 class="brand-name">Face<span>Siswa</span></h1>
            </div>
            
            <ul class="menu-list">
                <li class="menu-item active">
                    <a href="../veve/siswa_dashboard.php"><i class="fa-solid fa-graduation-cap"></i>Portal Personal</a>
                </li>
                <li class="menu-item">
                    <a href="../fiis/absensi.php" style="color: var(--primary);"><i class="fa-solid fa-camera"></i>Scan Kehadiran</a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <button class="btn-sidebar-action" onclick="toggleTheme()" id="themeBtn">
                <i class="fa-solid fa-moon"></i>
                <span id="themeBtnText">Mode Terang</span>
            </button>
            <a href="../desta/logout.php" class="btn-sidebar-action btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Dashboard Area -->
    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-box">
                <h2>Halo, <?php echo htmlspecialchars($siswa['nama']); ?>! 👋</h2>
                <p>Selamat datang di portal personal absensi cerdas Anda.</p>
            </div>
            <div class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="info-grid">
            <div class="info-card">
                <div class="info-icon indigo"><i class="fa-solid fa-id-card"></i></div>
                <div class="info-text">
                    <h3>NIS Anda</h3>
                    <p style="font-size: 20px; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($siswa['nis']); ?></p>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon blue"><i class="fa-solid fa-school"></i></div>
                <div class="info-text">
                    <h3>Kelas</h3>
                    <p style="font-size: 20px; font-weight: 700; color: var(--secondary);"><?php echo htmlspecialchars($siswa['kelas']); ?></p>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="info-text">
                    <h3>Total Kehadiran</h3>
                    <p><?php echo $totalHadir; ?></p>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon red"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="info-text">
                    <h3>Total Terlambat</h3>
                    <p><?php echo $totalLambat; ?></p>
                </div>
            </div>
        </section>

        <!-- Quick Action Grid -->
        <section class="quick-action-row">
            <div class="action-card">
                <div class="action-header">
                    <i class="fa-solid fa-camera"></i>
                    <h4>Scan Kehadiran Hari Ini</h4>
                </div>
                <p>Kehadiran dicatat secara instan dan otomatis menggunakan pemindai wajah cerdas di sekolah. Cukup klik tombol di bawah untuk membuka pemindai.</p>
                <a href="../fiis/absensi.php" class="action-btn">Mulai Pemindaian Wajah</a>
            </div>
        </section>

        <!-- Riwayat Absensi -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fa-solid fa-list-check" style="color: var(--primary); margin-right: 8px;"></i>Riwayat Kehadiran Pribadi</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Perekaman</th>
                        <th>Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($riwayat) > 0): ?>
                        <?php foreach ($riwayat as $row): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--secondary);"><?php echo htmlspecialchars(date('d F Y', strtotime($row['tanggal']))); ?></td>
                                <td style="font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($row['jam']); ?> WIB</td>
                                <td>
                                    <span class="badge <?php echo $row['status'] === 'Hadir' ? 'badge-hadir' : 'badge-lambat'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding: 40px; text-align: center; color: var(--text-secondary);">
                                <i class="fa-solid fa-clock-rotate-left" style="font-size: 24px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                Belum ada catatan absensi yang tercatat untuk Anda.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
