<?php
session_start();

if (!isset($_SESSION['admin_user'])) {
    header("Location: ../desta/login.php");
    exit;
}

$admin = $_SESSION['admin_user'];
include "../config/koneksi.php";

$dbStatus = false;
$dbError = "";
$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT');
$dbName = getenv('DB_NAME');

$stats = [
    'kelas' => 0,
    'admin' => 0,
    'siswa' => 0,
    'absensi' => 0
];

try {
    if (isset($koneksi)) {
        $dbStatus = true;
        
        // Count tables
        foreach (array_keys($stats) as $table) {
            try {
                $q = $koneksi->query("SELECT COUNT(*) FROM $table");
                $stats[$table] = $q->fetchColumn();
            } catch (PDOException $ex) {
                $stats[$table] = "Belum Ada";
            }
        }
    }
} catch (Exception $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konsol Database Supabase – Face Absensi</title>
<link rel="manifest" href="../manifest.json">
<meta name="theme-color" content="#3ecf8e">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
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
    --bg-dark: #060814;
    --bg-gradient: radial-gradient(circle at top, #0f1c18 0%, #060814 100%);
    --card-bg: #0b1512; 
    --card-border: #142a22; 
    --primary: #3ecf8e; /* Supabase Emerald Green */
    --primary-hover: #2eb87a;
    --primary-glow: rgba(62, 207, 142, 0.3);
    --secondary: #6366f1; /* Admin Purple */
    --secondary-hover: #4f46e5;
    --secondary-glow: rgba(99, 102, 241, 0.3);
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --sidebar-bg: #050b08;
    --sidebar-border: #0e1e19;
    --active-menu: rgba(62, 207, 142, 0.15);
    --input-bg: #05070f;
    --console-bg: #030708;
}

html[data-theme="light"] {
    --bg-dark: #f0fdf4;
    --bg-gradient: radial-gradient(circle at top, #dcfce7 0%, #f0fdf4 100%);
    --card-bg: #ffffff;
    --card-border: #bbf7d0; 
    --primary: #10b981;
    --primary-hover: #059669;
    --primary-glow: rgba(16, 185, 129, 0.15);
    --secondary: #4f46e5;
    --secondary-hover: #4338ca;
    --secondary-glow: rgba(79, 70, 229, 0.15);
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --sidebar-bg: #ffffff;
    --sidebar-border: #cbd5e1;
    --active-menu: rgba(16, 185, 129, 0.08);
    --input-bg: #f1f5f9;
    --console-bg: #0f172a;
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

.hamburger {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-primary);
}

/* Supabase Connection Widget */
.status-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--card-bg);
    border: 2px solid var(--card-border);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.status-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 13.5px;
    font-weight: 700;
}

.status-badge.connected {
    background: rgba(62, 207, 142, 0.12);
    color: var(--primary);
    border: 1px solid rgba(62, 207, 142, 0.2);
}

.status-badge.disconnected {
    background: rgba(239, 68, 68, 0.12);
    color: var(--danger);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.status-pulse {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    animation: pulse 1.5s infinite;
}

.status-pulse.connected { background: var(--primary); box-shadow: 0 0 10px var(--primary); }
.status-pulse.disconnected { background: var(--danger); box-shadow: 0 0 10px var(--danger); }

@keyframes pulse {
    0% { transform: scale(0.9); opacity: 0.6; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.6; }
}

/* Grid DB Info */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: var(--card-bg);
    border: 2px solid var(--card-border);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.info-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--card-border);
}

.info-icon.primary { color: var(--primary); background: rgba(62, 207, 142, 0.08); }
.info-icon.secondary { color: var(--secondary); background: rgba(99, 102, 241, 0.08); }

.info-details h4 {
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.info-details p {
    font-size: 16px;
    font-weight: 700;
}

/* Console Section */
.console-section {
    background: var(--card-bg);
    border: 2px solid var(--card-border);
    border-radius: 24px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

.console-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.console-title h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 20px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-container {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.console-btn {
    padding: 12px 24px;
    border-radius: 12px;
    border: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.console-btn.primary {
    background: var(--primary);
    color: #060814;
    box-shadow: 0 4px 15px var(--primary-glow);
}
.console-btn.primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(62, 207, 142, 0.45);
}

.console-btn.secondary {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    border: 1px solid var(--card-border);
}
.console-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

/* Code Console Output window */
.terminal-window {
    background: var(--console-bg);
    border: 1px solid #142a22;
    border-radius: 16px;
    font-family: 'Fira Code', monospace;
    font-size: 13.5px;
    overflow: hidden;
    box-shadow: inset 0 4px 10px rgba(0,0,0,0.8);
}

.terminal-header {
    background: rgba(255, 255, 255, 0.03);
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.terminal-dots {
    display: flex;
    gap: 6px;
}

.terminal-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.terminal-dot.red { background: #ef4444; }
.terminal-dot.yellow { background: #f59e0b; }
.terminal-dot.green { background: #10b981; }

.terminal-title {
    color: var(--text-secondary);
    font-size: 12px;
}

.terminal-body {
    padding: 20px;
    height: 280px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    color: #e2e8f0;
}

.log-line {
    line-height: 1.5;
    animation: fadeLine 0.2s ease-out forwards;
}

@keyframes fadeLine {
    from { opacity: 0; transform: translateX(-5px); }
    to { opacity: 1; transform: translateX(0); }
}

.log-info { color: #94a3b8; }
.log-success { color: #3ecf8e; font-weight: 600; }
.log-warning { color: #f59e0b; }
.log-error { color: #ef4444; font-weight: 700; }

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
                <li class="menu-item">
                    <a href="../dwi/siswa.php"><i class="fa-solid fa-users-gear"></i>Kelola Siswa</a>
                </li>
                <li class="menu-item">
                    <a href="../hasbi/rekap.php"><i class="fa-solid fa-chart-line"></i>Rekap Absensi</a>
                </li>
                <li class="menu-item active" style="margin-top: 15px; border-top: 1px solid var(--sidebar-border); padding-top: 15px;">
                    <a href="../aldi/database_console.php" style="color: var(--primary);"><i class="fa-solid fa-database"></i>Konsol Database</a>
                </li>
                <li class="menu-item">
                    <a href="../fiis/absensi.php" style="color: #6366f1;"><i class="fa-solid fa-camera"></i>Scan Kehadiran</a>
                </li>
                <li class="menu-item">
                    <a href="../fiis/register.php" style="color: #0ea5e9;"><i class="fa-solid fa-user-plus"></i>Registrasi Wajah</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header">
            <div class="welcome-box">
                <h2>Konsol Database Supabase</h2>
                <p>Monitor status, data tabel, dan kelola migrasi skema database PostgreSQL Supabase.</p>
            </div>
            <div class="hamburger" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </div>
        </header>

        <!-- Connection Status -->
        <section class="status-banner">
            <div class="status-info">
                <i class="fa-solid fa-server" style="font-size: 28px; color: var(--primary);"></i>
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 18px;">Supabase PostgreSQL</h3>
                    <p style="color: var(--text-secondary); font-size: 13.5px; margin-top: 2px;">Host: <?php echo htmlspecialchars($dbHost); ?>:<?php echo htmlspecialchars($dbPort); ?></p>
                </div>
            </div>
            <div>
                <?php if ($dbStatus): ?>
                    <span class="status-badge connected">
                        <span class="status-pulse connected"></span> Terhubung
                    </span>
                <?php else: ?>
                    <span class="status-badge disconnected">
                        <span class="status-pulse disconnected"></span> Terputus
                    </span>
                <?php endif; ?>
            </div>
        </section>

        <!-- Stats Grid -->
        <section class="info-grid">
            <div class="info-card">
                <div class="info-icon primary"><i class="fa-solid fa-users"></i></div>
                <div class="info-details">
                    <h4>Tabel Siswa</h4>
                    <p><?php echo $stats['siswa']; ?> baris</p>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon secondary"><i class="fa-solid fa-school"></i></div>
                <div class="info-details">
                    <h4>Tabel Kelas</h4>
                    <p><?php echo $stats['kelas']; ?> baris</p>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon secondary"><i class="fa-solid fa-user-shield"></i></div>
                <div class="info-details">
                    <h4>Tabel Admin</h4>
                    <p><?php echo $stats['admin']; ?> baris</p>
                </div>
            </div>
            <div class="info-card">
                <div class="info-icon primary"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="info-details">
                    <h4>Tabel Absensi</h4>
                    <p><?php echo $stats['absensi']; ?> baris</p>
                </div>
            </div>
        </section>

        <!-- Interactive Console -->
        <section class="console-section">
            <div class="console-title">
                <h3><i class="fa-solid fa-terminal" style="color: var(--primary);"></i> Utilitas Migrasi</h3>
                <span style="color: var(--text-secondary); font-size: 13px; font-weight: 500;">Jalankan query skema secara instan</span>
            </div>
            <div class="btn-container">
                <button class="console-btn primary" onclick="runMigration('default')">
                    <i class="fa-solid fa-play"></i> Jalankan Migrasi Utama
                </button>
                <button class="console-btn secondary" onclick="runMigration('columns')">
                    <i class="fa-solid fa-arrows-split-up-and-left"></i> Migrasi Kolom Tambahan
                </button>
                <button class="console-btn secondary" onclick="clearConsole()">
                    <i class="fa-solid fa-trash-can"></i> Bersihkan Log
                </button>
            </div>

            <!-- Terminal output window -->
            <div class="terminal-window">
                <div class="terminal-header">
                    <div class="terminal-dots">
                        <span class="terminal-dot red"></span>
                        <span class="terminal-dot yellow"></span>
                        <span class="terminal-dot green"></span>
                    </div>
                    <div class="terminal-title">supabase_migrations_log.sh</div>
                    <div><i class="fa-solid fa-expand" style="color: var(--text-secondary); font-size: 11px;"></i></div>
                </div>
                <div class="terminal-body" id="consoleBody">
                    <div class="log-line log-info">> Menunggu instruksi migrasi... Silakan klik tombol di atas.</div>
                </div>
            </div>
        </section>
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

updateThemeUI(savedTheme);

function clearConsole() {
    const consoleBody = document.getElementById('consoleBody');
    consoleBody.innerHTML = '<div class="log-line log-info">> Konsol dibersihkan. Menunggu instruksi...</div>';
}

function addLog(message, status) {
    const consoleBody = document.getElementById('consoleBody');
    const line = document.createElement('div');
    line.className = `log-line log-${status}`;
    line.textContent = `> ${message}`;
    consoleBody.appendChild(line);
    consoleBody.scrollTop = consoleBody.scrollHeight;
}

function runMigration(type) {
    addLog(`Mengirimkan perintah migrasi: [tipe: ${type}]...`, 'info');
    
    fetch(`../api/run_migration.php?type=${type}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.steps && data.steps.length > 0) {
                let delay = 0;
                data.steps.forEach(step => {
                    setTimeout(() => {
                        addLog(step.message, step.status);
                    }, delay);
                    delay += 250; // visual typing speed effect
                });
            } else {
                addLog('Respon kosong diterima dari server.', 'warning');
            }
        })
        .catch(error => {
            addLog(`Error saat mengeksekusi migrasi: ${error.message}`, 'error');
        });
}
</script>
</body>
</html>
