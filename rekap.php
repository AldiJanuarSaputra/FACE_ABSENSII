<?php
include "koneksi.php";

// Ambil parameter filter dari GET
$cari    = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$kelas   = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
$tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

$pesanSukses = '';
// ── Handle Hapus Log Absensi (Delete) ──────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $koneksi->prepare("DELETE FROM absensi WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $pesanSukses = "Log absensi berhasil dihapus!";
    } catch (PDOException $e) {
        $errorDb = "Gagal menghapus log absensi: " . $e->getMessage();
    }
}

// 1. Hitung statistik
try {
    // Total Siswa
    $qSiswa = $koneksi->query("SELECT COUNT(*) FROM siswa");
    $totalSiswa = $qSiswa->fetchColumn();

    // Total Hadir
    $qHadir = $koneksi->query("SELECT COUNT(*) FROM absensi WHERE status = 'Hadir'");
    $totalHadir = $qHadir->fetchColumn();

    // Total Terlambat
    $qLambat = $koneksi->query("SELECT COUNT(*) FROM absensi WHERE status = 'Terlambat'");
    $totalLambat = $qLambat->fetchColumn();
} catch (PDOException $e) {
    $totalSiswa = 0;
    $totalHadir = 0;
    $totalLambat = 0;
}

// 2. Query Kelas Unik untuk Dropdown Filter
$listKelas = [];
try {
    $qKl = $koneksi->query("SELECT DISTINCT kelas FROM siswa UNION SELECT DISTINCT kelas FROM absensi ORDER BY kelas ASC");
    while($rk = $qKl->fetch(PDO::FETCH_ASSOC)) {
        if(!empty($rk['kelas'])) $listKelas[] = $rk['kelas'];
    }
} catch (PDOException $e) {}

// 3. Query Utama Laporan Absensi
$logs = [];
try {
    $sql = "SELECT id, nis, nama, kelas, tanggal, jam, status FROM absensi WHERE 1=1";
    $params = [];

    if ($cari !== '') {
        $sql .= " AND (nama ILIKE :cari OR nis ILIKE :cari)";
        $params[':cari'] = "%$cari%";
    }

    if ($kelas !== '') {
        $sql .= " AND kelas = :kelas";
        $params[':kelas'] = $kelas;
    }

    if ($tanggal !== '') {
        $sql .= " AND tanggal = :tanggal";
        $params[':tanggal'] = $tanggal;
    }

    $sql .= " ORDER BY tanggal DESC, jam DESC";
    $stmt = $koneksi->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorDb = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Absensi – Face Absensi</title>
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
    --bg-dark: #090f1d;
    --bg-gradient: radial-gradient(circle at top, #1e1b4b 0%, #090f1d 100%);
    --card-bg: rgba(15, 23, 42, 0.65);
    --card-border: rgba(99, 102, 241, 0.28); /* Outline komponen ultra-jelas bernuansa Indigo */
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
    --sidebar-bg: rgba(10, 15, 30, 0.85);
    --sidebar-border: rgba(99, 102, 241, 0.25); /* Pembatas sidebar ultra-jelas */
    --active-menu: rgba(99, 102, 241, 0.2);
    --input-bg: rgba(10, 15, 30, 0.6);
    --row-border: rgba(99, 102, 241, 0.15); /* Garis pembatas baris tabel ultra-jelas */
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
    --warning: #f59e0b;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --sidebar-bg: rgba(255, 255, 255, 0.65);
    --sidebar-border: rgba(99, 102, 241, 0.08);
    --active-menu: rgba(79, 70, 229, 0.08);
    --input-bg: rgba(99, 102, 241, 0.03);
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
    border: 1px solid var(--card-border);
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

/* Grid Statistik */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.card-total {
    background: rgba(14, 165, 233, 0.12);
    color: var(--secondary);
    border: 1px solid rgba(14, 165, 233, 0.2);
}

.card-hadir {
    background: rgba(16, 185, 129, 0.12);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.card-lambat {
    background: rgba(239, 68, 68, 0.12);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.card-info h3 { 
    font-family: 'Outfit', sans-serif;
    font-size: 12.5px; 
    color: var(--text-secondary); 
    text-transform: uppercase; 
    letter-spacing: 0.5px; 
    font-weight: 700;
}
.card-info p { 
    font-family: 'Outfit', sans-serif;
    font-size: 28px; 
    font-weight: 800; 
}

/* Filter Box */
.filter-wrap {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    font-weight: 700;
}

.filter-group input, .filter-group select {
    width: 100%;
    padding: 12px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    outline: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.3s ease;
}

.filter-group input:focus, .filter-group select:focus {
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.08);
}

.filter-group select option { background: #0f172a; color: #fff; }

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
    transform: translateY(-2px); 
}

.btn-reset {
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    color: var(--text-primary);
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
}
.btn-reset:hover { 
    background: rgba(255, 255, 255, 0.1); 
    border-color: rgba(255, 255, 255, 0.2);
}

.btn-print {
    padding: 12px 20px;
    background: var(--secondary);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--secondary-glow);
}
.btn-print:hover { 
    background: var(--secondary-hover);
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);
    transform: translateY(-2px); 
}

/* Table Section */
.table-container {
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    overflow-x: auto;
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
    border-bottom: 1px solid var(--card-border);
}

td {
    padding: 16px 20px;
    font-size: 14px;
    border-bottom: 1px solid var(--row-border);
}

tr:hover td {
    background: rgba(255, 255, 255, 0.01);
}

/* Badges */
.badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
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

.empty-state {
    padding: 50px;
    text-align: center;
    color: var(--text-secondary);
    font-size: 16px;
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

/* Print Styles */
@media print {
    body { background: #fff !important; color: #000 !important; padding: 0; }
    .layout-wrapper { display: block !important; }
    .sidebar, header, .filter-wrap, .btn-print, .btn-reset, .no-print { display: none !important; }
    .main-content { padding: 0 !important; }
    .stats-grid { grid-template-columns: repeat(3, 1fr) !important; margin-bottom: 20px !important; }
    .card { background: none !important; border: 1px solid #ddd !important; color: #000 !important; box-shadow: none !important; }
    .card-icon { border: 1px solid #ddd !important; background: none !important; color: #000 !important; }
    .card-info h3 { color: #555 !important; }
    .card-info p { color: #000 !important; }
    .table-container { background: none !important; border: 1px solid #ccc !important; box-shadow: none !important; }
    th { background: #f0f0f0 !important; color: #000 !important; border-bottom: 2px solid #ccc !important; }
    td { border-bottom: 1px solid #eee !important; color: #000 !important; }
    .badge-hadir { background: none !important; color: green !important; border: 1px solid green !important; }
    .badge-lambat { background: none !important; color: red !important; border: 1px solid red !important; }
    
    body::before {
        content: "REKAPITULASI LAPORAN ABSENSI FACE ID";
        display: block;
        text-align: center;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #000;
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
                    <a href="index.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                </li>
                <li class="menu-item">
                    <a href="siswa.php"><i class="fa-solid fa-users-gear"></i>Kelola Siswa</a>
                </li>
                <li class="menu-item active">
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

        <div class="sidebar-footer">
            <button class="theme-toggle-btn" onclick="toggleTheme()" id="themeBtn">
                <i class="fa-solid fa-moon"></i>
                <span id="themeBtnText">Mode Terang</span>
            </button>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Header -->
        <header class="dashboard-header no-print">
            <div class="welcome-box">
                <h2>Rekapitulasi Absensi</h2>
                <p>Pantau data statistik kehadiran siswa dan cetak laporan resmi.</p>
            </div>
            <div class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </div>
        </header>

        <!-- Statistik Cards -->
        <div class="stats-grid">
            <div class="card">
                <div class="card-icon card-total"><i class="fa-solid fa-users"></i></div>
                <div class="card-info">
                    <h3>Total Siswa</h3>
                    <p><?php echo $totalSiswa; ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon card-hadir"><i class="fa-solid fa-circle-check"></i></div>
                <div class="card-info">
                    <h3>Total Hadir</h3>
                    <p><?php echo $totalHadir; ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon card-lambat"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="card-info">
                    <h3>Total Terlambat</h3>
                    <p><?php echo $totalLambat; ?></p>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-wrap no-print">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="cari">Cari Siswa</label>
                    <input type="text" id="cari" name="cari" placeholder="Nama / NIS..." value="<?php echo htmlspecialchars($cari); ?>">
                </div>

                <div class="filter-group">
                    <label for="kelas">Filter Kelas</label>
                    <select id="kelas" name="kelas">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($listKelas as $k): ?>
                            <option value="<?php echo htmlspecialchars($k); ?>" <?php echo $kelas === $k ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($k); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="tanggal">Filter Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($tanggal); ?>">
                </div>

                <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i>Filter</button>
                <a href="rekap.php" class="btn-reset"><i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i>Reset</a>
                <button type="button" class="btn-print" onclick="window.print()"><i class="fa-solid fa-print" style="margin-right: 6px;"></i>Cetak</button>
            </form>
        </div>

        <?php if(isset($errorDb)): ?>
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.2); padding: 15px; border-radius: 16px; margin-bottom: 20px; color: var(--danger);">
                <i class="fa-solid fa-circle-exclamation" style="margin-right: 8px;"></i><strong>Error Database:</strong> <?php echo htmlspecialchars($errorDb); ?>
            </div>
        <?php endif; ?>

        <?php if($pesanSukses !== ''): ?>
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.2); padding: 15px; border-radius: 16px; margin-bottom: 20px; color: var(--success); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo htmlspecialchars($pesanSukses); ?></span>
            </div>
        <?php endif; ?>

        <!-- Table Absensi -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Jam Absen</th>
                        <th style="width: 120px; text-align: center;">Status</th>
                        <th style="width: 100px; text-align: center;" class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php $no = 1; foreach ($logs as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td style="font-weight: 700;"><?php echo htmlspecialchars($row['nis']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><span style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--card-border); padding: 4px 10px; border-radius: 8px; font-size: 12.5px; font-weight: 600; color: var(--text-secondary);"><?php echo htmlspecialchars($row['kelas']); ?></span></td>
                                <td><?php echo date("d/m/Y", strtotime($row['tanggal'])); ?></td>
                                <td style="font-family: monospace; font-size: 14.5px; color: var(--secondary); font-weight: 700;"><?php echo htmlspecialchars($row['jam']); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($row['status'] === 'Hadir'): ?>
                                        <span class="badge badge-hadir">Hadir</span>
                                    <?php else: ?>
                                        <span class="badge badge-lambat">Terlambat</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;" class="no-print">
                                    <button class="btn-delete" onclick="konfirmasiHapus(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama']); ?>', '<?php echo date('d/m/Y', strtotime($row['tanggal'])); ?>')" style="padding: 6px 12px; font-size: 12px; background: rgba(239, 68, 68, 0.12); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px; cursor: pointer; transition: all 0.3s ease; font-weight: 700;">
                                        <i class="fa-solid fa-trash" style="margin-right: 4px;"></i>Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fa-solid fa-inbox" style="margin-right: 8px;"></i>Tidak ada data absensi yang ditemukan.
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

function konfirmasiHapus(id, nama, tanggal) {
    if (confirm("Apakah Anda yakin ingin menghapus log absensi siswa '" + nama + "' pada tanggal " + tanggal + "?")) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('action', 'delete');
        urlParams.set('id', id);
        window.location.href = "rekap.php?" + urlParams.toString();
    }
}
</script>
</body>
</html>
