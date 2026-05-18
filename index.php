<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Face ID Absensi</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#ff1493">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js')
      .then(reg => console.log('Service Worker registered!', reg.scope))
      .catch(err => console.log('Service Worker failed!', err));
  });
}
</script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
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
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--bg-gradient);
    color: var(--text-primary);
    padding: 20px;
    overflow-x: hidden;
}

.container {
    width: 100%;
    max-width: 440px;
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 24px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 
                inset 0 1px 1px rgba(255, 255, 255, 0.08);
}

h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 25px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    color: var(--text-primary);
}

p.sub {
    color: var(--text-secondary);
    font-size: 13.5px;
    margin-bottom: 28px;
    font-weight: 500;
}

.nav-links {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}

.nav-links a {
    flex: 1;
    padding: 14px 12px;
    border-radius: 16px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13.5px;
    color: #fff;
    background: var(--primary);
    box-shadow: 0 4px 15px var(--primary-glow);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.nav-links a:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

.nav-links a.sec {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--card-border);
    color: var(--text-primary);
    box-shadow: none;
}

.nav-links a.sec:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: none;
}

.btn-full {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    border-radius: 16px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--card-border);
    transition: all 0.3s ease;
    margin-bottom: 12px;
}

.btn-full:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.btn-full.active-btn {
    background: var(--secondary);
    border: none;
    box-shadow: 0 4px 15px var(--secondary-glow);
}

.btn-full.active-btn:hover {
    background: var(--secondary-hover);
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);
}

.info-box {
    margin-top: 24px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    padding: 16px;
    text-align: left;
}

.info-box h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 13.5px;
    color: var(--text-primary);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
}

.info-box p {
    color: var(--text-secondary);
    font-size: 12.5px;
    line-height: 1.6;
}
</style>
</head>
<body>
<div class="container">
    <h1><i class="fa-solid fa-face-viewfinder" style="color: var(--primary); margin-right: 4px;"></i>Sistem Absensi</h1>
    <p class="sub">Selamat datang di Panel Kontrol Absensi Face ID</p>

    <div class="nav-links">
        <a href="absensi.php"><i class="fa-solid fa-camera" style="font-size: 18px;"></i>Scan Absensi</a>
        <a href="register.php" class="sec"><i class="fa-solid fa-user-plus" style="font-size: 18px;"></i>Daftar Wajah</a>
    </div>
    
    <a href="siswa.php" class="btn-full"><i class="fa-solid fa-users-gear" style="font-size: 15px;"></i>Kelola Data Siswa</a>
    <a href="rekap.php" class="btn-full active-btn"><i class="fa-solid fa-chart-simple" style="font-size: 15px;"></i>Rekapitulasi Absensi</a>

    <div class="info-box">
        <h3><i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Panduan Cepat</h3>
        <p>
            • Pilih <strong>Scan Absensi</strong> untuk memulai proses pemindaian kehadiran siswa.<br>
            • Pilih <strong>Daftar Wajah</strong> untuk menambahkan data wajah siswa baru ke database.
        </p>
    </div>
</div>
</body>
</html>