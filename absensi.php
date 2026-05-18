<?php
session_start();
include "koneksi.php";
date_default_timezone_set("Asia/Jakarta");
$hariIni = date("Y-m-d");
$riwayatHariIni = [];
try {
    $stmt = $koneksi->prepare("SELECT nama, kelas, jam, status FROM absensi WHERE tanggal = :tanggal ORDER BY jam DESC");
    $stmt->execute([':tanggal' => $hariIni]);
    $riwayatHariIni = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Absensi Face ID</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#6366f1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script defer src="js/face-api.min.js"></script>
<script>
    // Theme Initializer
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
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

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
    --warning: #f59e0b;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
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
}

/* Floating theme toggle button */
.theme-toggle-floating {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    z-index: 100;
}
.theme-toggle-floating:hover {
    transform: scale(1.05);
    background: rgba(255, 255, 255, 0.1);
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--bg-gradient);
    overflow-x: hidden;
    color: var(--text-primary);
    padding: 15px;
}

/* Mobile-First Layout container */
.container {
    width: 100%;
    max-width: 440px;
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 24px;
    padding: 24px;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6),
                inset 0 1px 1px rgba(255, 255, 255, 0.08);
    position: relative;
    overflow: hidden;
}

/* Header typography */
h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    color: var(--text-primary);
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.subtitle {
    color: var(--text-secondary);
    font-size: 13.5px;
    margin-bottom: 24px;
    font-weight: 500;
    letter-spacing: 0.2px;
    position: relative;
    z-index: 1;
}

/* Camera Scanner Container */
.video-wrap {
    position: relative;
    display: inline-block;
    border-radius: 20px;
    overflow: hidden;
    padding: 5px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15);
    margin-bottom: 12px;
    z-index: 1;
    width: 100%;
}

video {
    width: 100%;
    height: auto;
    aspect-ratio: 4/3;
    border-radius: 14px;
    background: #000;
    display: block;
    object-fit: cover;
}

canvas.overlay {
    position: absolute;
    top: 5px;
    left: 5px;
    width: calc(100% - 10px);
    height: calc(100% - 10px);
    border-radius: 14px;
    pointer-events: none;
}

/* Sweeping Scanning Line */
.video-wrap::after {
    content: '';
    position: absolute;
    top: 5px;
    left: 5px;
    width: calc(100% - 10px);
    height: 2.5px;
    background: linear-gradient(to right, transparent, var(--primary), #ffffff, var(--primary), transparent);
    animation: scan 4s ease-in-out infinite;
    opacity: 0.8;
    pointer-events: none;
    z-index: 10;
}

@keyframes scan {
    0% { top: 5px; }
    50% { top: calc(100% - 7.5px); }
    100% { top: 5px; }
}

/* Button & Controls Redesign */
.btn-row {
    display: flex;
    gap: 12px;
    margin-top: 14px;
    position: relative;
    z-index: 1;
}

button {
    flex: 1;
    padding: 14px 16px;
    border: none;
    border-radius: 16px;
    font-size: 13.5px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    background: var(--primary);
    box-shadow: 0 4px 15px var(--primary-glow);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

button:hover:not(:disabled) {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

button:active:not(:disabled) {
    transform: translateY(1px);
}

button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-reg {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--card-border);
    color: var(--text-primary);
    box-shadow: none;
}

.btn-reg:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: none;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.06) !important;
    border: 1px solid var(--card-border) !important;
    margin-top: 12px;
    width: 100%;
    color: var(--text-primary);
    font-weight: 700;
    border-radius: 16px;
    padding: 14px;
    cursor: pointer;
    box-shadow: none;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.25) !important;
    transform: translateY(-2px);
}

/* Status Text Styles */
#status {
    margin-top: 16px;
    font-size: 13.5px;
    font-weight: 500;
    min-height: 24px;
    line-height: 1.6;
    position: relative;
    z-index: 1;
}

.ok { color: var(--success); }
.err { color: var(--danger); }
.info { color: #ffe066; }

/* Scan Result Box - sophisticated styling */
#hasil-box {
    display: none;
    margin-top: 16px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 16px;
    font-size: 13.5px;
    color: var(--text-primary);
    text-align: left;
    line-height: 1.8;
    animation: fadeIn 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1;
}

#hasil-box.show {
    display: block;
}

#hasil-box strong {
    font-family: 'Outfit', sans-serif;
    color: var(--secondary);
    font-weight: 800;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 6px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 4px;
}

/* Mini Rekap Box */
#mini-rekap-box {
    margin-top: 24px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    padding: 16px;
    text-align: left;
    position: relative;
    z-index: 1;
}

#mini-rekap-box h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 13.5px;
    color: var(--text-primary);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
}

#mini-rekap-box table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}

#mini-rekap-box th {
    padding: 8px 6px;
    color: var(--text-secondary);
    font-weight: 600;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

#mini-rekap-box td {
    padding: 8px 6px;
    color: var(--text-primary);
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

/* Badges */
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

/* Splash Screen Redesign */
.splash-screen {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: var(--bg-gradient);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
}
.splash-screen.fade-out {
    opacity: 0;
    visibility: hidden;
}
.splash-content {
    text-align: center;
}
.splash-logo {
    font-size: 80px;
    color: var(--primary);
    animation: pulse 2s infinite ease-in-out;
    margin-bottom: 20px;
}
.splash-title {
    font-family: 'Outfit', sans-serif;
    color: #fff;
    font-size: 24px;
    letter-spacing: 4px;
    font-weight: 800;
    margin-bottom: 30px;
    text-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
}
.splash-spinner {
    width: 48px;
    height: 48px;
    border: 3.5px solid rgba(99, 102, 241, 0.1);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s cubic-bezier(0.55, 0.085, 0.68, 0.53) infinite;
    margin: 0 auto 24px;
}
.splash-status {
    font-family: 'Outfit', sans-serif;
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 500;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.06); opacity: 1; }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<button class="theme-toggle-floating" onclick="toggleTheme()" id="themeBtn" aria-label="Toggle Theme">
    <i class="fa-solid fa-moon"></i>
</button>

<!-- PWA Splash Screen overlay -->
<div id="splash-screen" class="splash-screen">
    <div class="splash-content">
        <div class="splash-logo">
            <i class="fa-solid fa-face-viewfinder"></i>
        </div>
        <h2 class="splash-title">FACE ID ABSENSI</h2>
        <div class="splash-spinner"></div>
        <p id="splash-status" class="splash-status">Mengaktifkan kamera & memuat model AI...</p>
    </div>
</div>

<div class="container">
    <h1><i class="fa-solid fa-face-viewfinder" style="color: var(--primary)"></i>Absensi Face ID</h1>
    <p class="subtitle">Posisikan wajah di depan kamera, lalu ketuk Scan</p>

    <div class="video-wrap">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="overlay" class="overlay"></canvas>
    </div>

    <button id="btnScan" onclick="scan()" disabled style="width: 100%; margin-top: 14px;"><i class="fa-solid fa-camera-retro"></i>Scan Wajah</button>
    <?php if (isset($_SESSION['admin_user'])): ?>
        <a href="index.php" style="text-decoration:none; display: block; width: 100%;">
            <button class="btn-secondary" type="button"><i class="fa-solid fa-arrow-left-long" style="margin-right: 6px;"></i>Kembali ke Menu Admin</button>
        </a>
    <?php else: ?>
        <a href="siswa_dashboard.php" style="text-decoration:none; display: block; width: 100%;">
            <button class="btn-secondary" type="button"><i class="fa-solid fa-arrow-left-long" style="margin-right: 6px;"></i>Kembali ke Dasbor Siswa</button>
        </a>
    <?php endif; ?>

    <p id="status" class="info">Memuat sistem...</p>

    <div id="hasil-box">
        <strong>Hasil Absensi:</strong>
        <span id="info-nama"></span><br>
        <span id="info-nis"></span><br>
        <span id="info-kelas"></span><br>
        <span id="info-waktu"></span><br>
        <span id="info-status"></span>
    </div>

    <!-- Mini Rekap Hari Ini -->
    <div id="mini-rekap-box">
        <h3><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Absen Hari Ini</h3>
        
        <div style="max-height: 180px; overflow-y: auto; padding-right: 3px;">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th style="text-align: center; width: 60px;">Jam</th>
                        <th style="text-align: center; width: 80px;">Status</th>
                    </tr>
                </thead>
                <tbody id="body-mini-rekap">
                    <?php if (count($riwayatHariIni) > 0): ?>
                        <?php foreach ($riwayatHariIni as $log): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($log['nama']); ?></td>
                                <td><?php echo htmlspecialchars($log['kelas']); ?></td>
                                <td style="text-align: center; color: var(--neon-pink); font-family: 'JetBrains Mono', monospace; font-size: 12px;"><?php echo htmlspecialchars($log['jam']); ?></td>
                                <td style="text-align: center;">
                                    <span class="badge <?php echo $log['status'] === 'Hadir' ? 'badge-hadir' : 'badge-lambat'; ?>">
                                        <?php echo htmlspecialchars($log['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="empty-mini-row">
                            <td colspan="4" style="padding: 20px; text-align: center; color: rgba(255,255,255,0.4);">
                                Belum ada riwayat absensi hari ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const video    = document.getElementById("video");
const overlay  = document.getElementById("overlay");
const statusEl = document.getElementById("status");
const btnScan  = document.getElementById("btnScan");
const hasilBox = document.getElementById("hasil-box");

let labeledDescriptors = [];
let isScanning = false;

// ── Splash screen hide ─────────────────────────────────
function hideSplash() {
    const splash = document.getElementById("splash-screen");
    if (splash) {
        splash.classList.add("fade-out");
        setTimeout(() => splash.remove(), 500);
    }
}

// ── Kamera ─────────────────────────────────────────────
async function startCamera(){
    try{
        const stream = await navigator.mediaDevices.getUserMedia({
            video:{width:360,height:270,facingMode:"user"},
            audio:false
        });
        video.srcObject = stream;
        return true;
    }catch(e){
        setStatus("❌ Kamera gagal: "+e.message,"err");
        setTimeout(hideSplash, 3000);
        return false;
    }
}

// ── Load model face-api ────────────────────────────────
async function loadModels(){
    setStatus("⏳ Memuat model AI...","info");
    try{
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("models"),
            faceapi.nets.faceLandmark68Net.loadFromUri("models"),
            faceapi.nets.faceRecognitionNet.loadFromUri("models")
        ]);
        setStatus("⏳ Memuat data wajah siswa...","info");
        await loadStudentDescriptors();
    }catch(e){
        setStatus("❌ Model gagal dimuat: "+e.message,"err");
        console.error(e);
        setTimeout(hideSplash, 3000);
    }
}

// ── Ambil descriptor siswa dari DB ─────────────────────
async function loadStudentDescriptors(){
    try{
        const res  = await fetch("get_siswa.php");
        const list = await res.json();

        if(!list || list.length === 0){
            setStatus("⚠️ Belum ada wajah terdaftar. Silakan <a href='register.php' style='color:#ff9edb'>daftar</a> dulu","info");
            btnScan.disabled = false;
            setTimeout(hideSplash, 1000);
            return;
        }

        labeledDescriptors = list.map(siswa => {
            const desc = new Float32Array(siswa.descriptor);
            return new faceapi.LabeledFaceDescriptors(
                siswa.nis + "|" + siswa.nama + "|" + siswa.kelas,
                [desc]
            );
        });

        btnScan.disabled = false;
        setStatus("✅ Sistem siap – "+list.length+" wajah terdaftar","ok");
        setTimeout(hideSplash, 600);
    }catch(e){
        setStatus("❌ Gagal memuat data siswa: "+e.message,"err");
        btnScan.disabled = false;
        console.error(e);
        setTimeout(hideSplash, 3000);
    }
}

// ── Scan & Match Wajah ─────────────────────────────────
async function scan(){
    if(isScanning) return;
    isScanning = true;
    btnScan.disabled = true;
    hasilBox.classList.remove("show");
    clearOverlay();

    setStatus("🔍 Mendeteksi wajah...","info");

    // Deteksi wajah
    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize:416,scoreThreshold:0.3}))
        .withFaceLandmarks()
        .withFaceDescriptor();

    if(!detection){
        setStatus("❌ Wajah tidak terdeteksi. Pastikan wajah terlihat jelas dan cahaya cukup","err");
        isScanning = false;
        btnScan.disabled = false;
        return;
    }

    // Gambar kotak deteksi
    const dims = faceapi.matchDimensions(overlay, video, true);
    faceapi.draw.drawDetections(overlay, faceapi.resizeResults(detection, dims));
    faceapi.draw.drawFaceLandmarks(overlay, faceapi.resizeResults(detection, dims));

    // Jika belum ada data wajah terdaftar
    if(labeledDescriptors.length === 0){
        setStatus("⚠️ Wajah terdeteksi, tapi belum ada data siswa terdaftar","info");
        isScanning = false;
        btnScan.disabled = false;
        return;
    }

    // Pencocokan wajah
    setStatus("🧠 Mencocokkan wajah...","info");
    const matcher  = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
    const bestMatch= matcher.findBestMatch(detection.descriptor);

    if(bestMatch.label === "unknown"){
        setStatus("⚠️ Wajah tidak dikenali (jarak: "+bestMatch.distance.toFixed(3)+"). Coba lagi atau daftarkan wajah","err");
        isScanning = false;
        btnScan.disabled = false;
        return;
    }

    // Parsed label: "NIS|NAMA|KELAS"
    const parts = bestMatch.label.split("|");
    const nis   = parts[0] || "";
    const nama  = parts[1] || "";
    const kelas = parts[2] || "";

    setStatus("✅ Wajah dikenali: <strong>"+nama+"</strong> – menyimpan absensi...","ok");

    // Simpan absensi
    try{
        const resp = await fetch("simpan_absen.php",{
            method:"POST",
            headers:{"Content-Type":"application/json"},
            body:JSON.stringify({nis, nama, kelas})
        });
        const result = await resp.json();

        if(result.sukses){
            setStatus("✅ Absensi berhasil!","ok");
            tampilkanHasil(result);
        }else{
            setStatus("⚠️ "+result.pesan,"info");
            tampilkanHasil(result);
        }
    }catch(e){
        setStatus("❌ Gagal menyimpan absensi: "+e.message,"err");
    }

    isScanning = false;
    btnScan.disabled = false;
}

function tampilkanHasil(r){
    document.getElementById("info-nama").innerHTML  = "<i class='fa-solid fa-user' style='color: #00bfff; margin-right: 8px; width: 18px;'></i>Nama   : <strong>"+r.nama+"</strong>";
    document.getElementById("info-nis").innerHTML   = "<i class='fa-solid fa-id-card' style='color: #00bfff; margin-right: 8px; width: 18px;'></i>NIS    : "+r.nis;
    document.getElementById("info-kelas").innerHTML = "<i class='fa-solid fa-school' style='color: #00bfff; margin-right: 8px; width: 18px;'></i>Kelas  : "+r.kelas;
    document.getElementById("info-waktu").innerHTML = "<i class='fa-solid fa-clock' style='color: #ff1493; margin-right: 8px; width: 18px;'></i>Waktu  : "+(r.waktu||"-");
    
    const statusColor = r.status === 'Hadir' ? '#7fff7f' : '#ff6b6b';
    document.getElementById("info-status").innerHTML= "<i class='fa-solid fa-circle-check' style='color: " + statusColor + "; margin-right: 8px; width: 18px;'></i>Status : <strong>"+(r.status||"-")+"</strong>";
    hasilBox.classList.add("show");

    // ── Prepend Riwayat Absen Hari Ini secara Real-Time ──
    const emptyRow = document.getElementById("empty-mini-row");
    if (emptyRow) emptyRow.remove();

    const bodyRekap = document.getElementById("body-mini-rekap");
    const newRow = document.createElement("tr");
    newRow.style.borderBottom = "1px solid rgba(255,255,255,0.05)";
    newRow.style.animation = "fadeIn 0.5s ease-out";
    
    const badgeClass = r.status === 'Hadir' ? 'badge-hadir' : 'badge-lambat';
    
    newRow.innerHTML = `
        <td style="padding: 8px 5px; font-weight: 600;">${r.nama}</td>
        <td style="padding: 8px 5px;">${r.kelas}</td>
        <td style="padding: 8px 5px; text-align: center; color: #ff9edb; font-family: monospace; font-size: 13px;">${r.waktu}</td>
        <td style="padding: 8px 5px; text-align: center;">
            <span class="badge ${badgeClass}" style="padding: 3px 8px; font-size: 10px; border-radius: 6px;">${r.status}</span>
        </td>
    `;
    
    bodyRekap.insertBefore(newRow, bodyRekap.firstChild);
}

function clearOverlay(){
    const ctx = overlay.getContext("2d");
    ctx.clearRect(0, 0, overlay.width, overlay.height);
}

function setStatus(msg, cls="info"){
    statusEl.className = cls;
    statusEl.innerHTML = msg;
    
    // Also update splash status if it's currently showing
    const splashStatus = document.getElementById("splash-status");
    if (splashStatus) {
        splashStatus.innerHTML = msg;
    }
}

// ── Theme Toggle Logic ──
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    updateThemeUI(newTheme);
}

function updateThemeUI(theme) {
    const themeBtn = document.getElementById('themeBtn');
    if (!themeBtn) return;
    
    if (theme === 'light') {
        themeBtn.innerHTML = '<i class="fa-solid fa-sun" style="color: #f59e0b;"></i>';
    } else {
        themeBtn.innerHTML = '<i class="fa-solid fa-moon"></i>';
    }
}

// Set correct toggle button UI on page load
updateThemeUI(savedTheme);

// ── Init ───────────────────────────────────────────────
(async()=>{
    const camOk = await startCamera();
    if(camOk) await loadModels();
})();
</script>

</body>
</html>
