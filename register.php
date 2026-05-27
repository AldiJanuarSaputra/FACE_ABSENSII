<?php
session_start();
include "config/koneksi.php";
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;

$siswa = null;
if ($siswa_id > 0) {
    $stmt = $koneksi->prepare("SELECT s.nis, s.nama, k.nama_kelas, k.id as kelas_id FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.id = :id");
    $stmt->execute([':id' => $siswa_id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($siswa) {
        $kelas_id = $siswa['kelas_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Wajah – Face Absensi</title>
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
    border: 2px solid var(--card-border);
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
    border: 2px solid var(--card-border);
    border-radius: 24px;
    padding: 24px;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6),
                inset 0 1px 1px rgba(255, 255, 255, 0.08);
    position: relative;
    overflow: hidden;
}

/* Header typography */
h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 20px;
    color: var(--text-primary);
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Inputs Redesign */
input {
    width: 100%;
    padding: 12px 16px;
    margin: 8px 0;
    border: 2px solid var(--card-border);
    outline: none;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.04);
    color: #fff;
    font-size: 14.5px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

input:focus {
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 12px var(--primary-glow);
}

input::placeholder {
    color: rgba(255, 255, 255, 0.4);
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
    margin-top: 15px;
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
button {
    width: 100%;
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
    margin-top: 15px;
    position: relative;
    z-index: 1;
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

.btn-secondary {
    background: rgba(255, 255, 255, 0.06) !important;
    border: 2px solid var(--card-border) !important;
    margin-top: 10px;
    color: var(--text-primary);
    font-weight: 700;
    border-radius: 16px;
    padding: 14px;
    cursor: pointer;
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
#hasil {
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
    <?php if ($siswa_id > 0 && $kelas_id > 0): ?>
        <a href="kelas_detail.php?id=<?php echo $kelas_id; ?>" style="display:inline-block; margin-bottom: 20px; color: var(--text-secondary); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Detail Kelas</a>
    <?php endif; ?>
    <h2><i class="fa-solid fa-user-plus" style="color: var(--primary)"></i>Registrasi Wajah</h2>

    <input type="text" id="nis"   placeholder="NIS Siswa" value="<?php echo $siswa ? htmlspecialchars($siswa['nis']) : ''; ?>" <?php echo $siswa ? 'readonly style="opacity:0.7; cursor:not-allowed;"' : ''; ?>>
    <input type="text" id="nama"  placeholder="Nama Lengkap" value="<?php echo $siswa ? htmlspecialchars($siswa['nama']) : ''; ?>" <?php echo $siswa ? 'readonly style="opacity:0.7; cursor:not-allowed;"' : ''; ?>>
    <input type="text" id="kelas" placeholder="Kelas (contoh: X-IPA-1)" value="<?php echo $siswa ? htmlspecialchars($siswa['nama_kelas']) : ''; ?>" <?php echo $siswa ? 'readonly style="opacity:0.7; cursor:not-allowed;"' : ''; ?>>
    <input type="hidden" id="kelas_id" value="<?php echo $kelas_id; ?>">
    <select id="tingkat" style="width:100%; padding:12px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:12px; color:#fff; font-size:14px; font-family:'Plus Jakarta Sans',sans-serif; outline:none; margin-bottom:10px;">
        <option value="" disabled selected>Pilih Tingkat</option>
        <option value="X">X (Kelas 10)</option>
        <option value="XI">XI (Kelas 11)</option>
        <option value="XII">XII (Kelas 12)</option>
    </select>
    <input type="text" id="jurusan" placeholder="Jurusan (contoh: IPA, IPS, TKJ, RPL)">
    <input type="password" id="password" placeholder="Buat Kata Sandi (Password)">

    <div class="video-wrap">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="overlay" class="overlay"></canvas>
    </div>

    <canvas id="canvas" style="display:none;" width="320" height="240"></canvas>

    <button id="btnDaftar" onclick="daftar()" disabled><i class="fa-solid fa-cloud-arrow-up"></i>Daftar Sekarang</button>
    <?php if (isset($_SESSION['admin_user'])): ?>
        <a href="index.php" style="text-decoration:none;"><button class="btn-secondary" type="button"><i class="fa-solid fa-house" style="margin-right: 6px;"></i>Ke Dasbor Admin</button></a>
    <?php elseif (isset($_SESSION['siswa_user'])): ?>
        <a href="siswa_dashboard.php" style="text-decoration:none;"><button class="btn-secondary" type="button"><i class="fa-solid fa-house" style="margin-right: 6px;"></i>Ke Portal Siswa</button></a>
    <?php else: ?>
        <a href="login.php" style="text-decoration:none;"><button class="btn-secondary" type="button"><i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i>Ke Halaman Login</button></a>
    <?php endif; ?>
    <a href="absensi.php" style="text-decoration:none;"><button class="btn-secondary" type="button" style="margin-top: 5px;"><i class="fa-solid fa-camera-retro" style="margin-right: 6px;"></i>Ke Halaman Absensi</button></a>

    <p id="hasil" class="info">Memuat sistem...</p>
</div>

<script>
const video    = document.getElementById("video");
const canvas   = document.getElementById("canvas");
const overlay  = document.getElementById("overlay");
const hasil    = document.getElementById("hasil");
const btnDaftar = document.getElementById("btnDaftar");

let modelsReady = false;
let isRealtimeTracking = false;

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
            video:{width:320,height:240,facingMode:"user"},
            audio:false
        });
        video.srcObject = stream;
        return true;
    }catch(e){
        setStatus("❌ Kamera tidak bisa diakses: "+e.message,"err");
        setTimeout(hideSplash, 3000);
        return false;
    }
}

// ── Load Model ─────────────────────────────────────────
async function loadModels(){
    try{
        setStatus("⏳ Memuat model AI...","info");
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("models"),
            faceapi.nets.faceLandmark68Net.loadFromUri("models"),
            faceapi.nets.faceRecognitionNet.loadFromUri("models")
        ]);
        modelsReady = true;
        btnDaftar.disabled = false;
        setStatus("✅ Kamera aktif – sistem siap","ok");
        setTimeout(hideSplash, 600);
    }catch(e){
        setStatus("❌ Model gagal dimuat: "+e.message,"err");
        console.error(e);
        setTimeout(hideSplash, 3000);
    }
}

// ── Registrasi ─────────────────────────────────────────
async function daftar(){
    const nis      = document.getElementById("nis").value.trim();
    const nama     = document.getElementById("nama").value.trim();
    const kelas    = document.getElementById("kelas").value.trim();
    const tingkat  = document.getElementById("tingkat").value;
    const jurusan  = document.getElementById("jurusan").value.trim();
    const password = document.getElementById("password").value.trim();
    const kelas_id = document.getElementById("kelas_id") ? document.getElementById("kelas_id").value : null;

    if(!nis || !nama || !kelas || !password){
        setStatus("⚠️ Lengkapi semua data dan password terlebih dahulu","err");
        return;
    }
    if(!modelsReady){
        setStatus("⚠️ Model AI belum siap, tunggu sebentar","info");
        return;
    }

    btnDaftar.disabled = true;
    isRealtimeTracking = false; // Hentikan tracking real-time sementara
    setStatus("🔍 Mendeteksi wajah...","info");

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize:320,scoreThreshold:0.2}))
        .withFaceLandmarks()
        .withFaceDescriptor();

    if(!detection){
        setStatus("❌ Wajah tidak terdeteksi, pastikan wajah terlihat jelas","err");
        isRealtimeTracking = true; // Hidupkan kembali tracking real-time
        startRealtimeTracking();
        btnDaftar.disabled = false;
        return;
    }

    // Gambar hasil deteksi ke overlay
    const dims = faceapi.matchDimensions(overlay, video, true);
    faceapi.draw.drawDetections(overlay, faceapi.resizeResults(detection, dims));

    // Ambil foto dari video
    const ctx = canvas.getContext("2d");
    canvas.width  = 320;
    canvas.height = 240;
    ctx.drawImage(video, 0, 0, 320, 240);
    const foto = canvas.toDataURL("image/jpeg", 0.8);

    // Ambil face descriptor (array 128 float)
    const descriptor = Array.from(detection.descriptor);

    setStatus("💾 Menyimpan data...","info");

    fetch("api/simpan_siswa.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({nis, nama, kelas, kelas_id, tingkat, jurusan, password, wajah:foto, descriptor})
    })
    .then(r=>r.text())
    .then(msg=>{
        if(msg.includes("berhasil")){
            setStatus("✅ "+msg,"ok");
            // Reset form jika bukan mode edit
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('siswa_id')) {
                document.getElementById("nis").value  = "";
                document.getElementById("nama").value = "";
                document.getElementById("kelas").value= "";
                document.getElementById("password").value= "";
            } else {
                setTimeout(() => window.location.href = "kelas_detail.php?id=" + document.getElementById("kelas_id").value, 1500);
            }
            // Bersihkan overlay setelah 2 detik
            setTimeout(()=>overlay.getContext("2d").clearRect(0,0,overlay.width,overlay.height),2000);
        }else{
            setStatus("❌ "+msg,"err");
        }
        isRealtimeTracking = true; // Hidupkan kembali tracking real-time
        startRealtimeTracking();
        btnDaftar.disabled = false;
    })
    .catch(()=>{
        setStatus("❌ Gagal menghubungi server","err");
        isRealtimeTracking = true; // Hidupkan kembali tracking real-time
        startRealtimeTracking();
        btnDaftar.disabled = false;
    });
}

function setStatus(msg, cls="info"){
    hasil.className = cls;
    hasil.innerHTML = msg;
    
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

// ── Realtime Face Tracking Feedback ────────────────────
async function startRealtimeTracking() {
    if (!isRealtimeTracking) return;
    
    try {
        const detection = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize: 320, scoreThreshold: 0.25}))
            .withFaceLandmarks();
            
        // Bersihkan overlay
        const ctx = overlay.getContext("2d");
        ctx.clearRect(0, 0, overlay.width, overlay.height);
        
        if (detection && isRealtimeTracking) {
            const dims = faceapi.matchDimensions(overlay, video, true);
            const resized = faceapi.resizeResults(detection, dims);
            faceapi.draw.drawDetections(overlay, resized);
            faceapi.draw.drawFaceLandmarks(overlay, resized);
        }
    } catch (e) {
        console.error("Error in realtime tracking loop:", e);
    }
    
    if (isRealtimeTracking) {
        setTimeout(startRealtimeTracking, 120);
    }
}

// ── Init ───────────────────────────────────────────────
(async()=>{
    const camOk = await startCamera();
    if(camOk) {
        await loadModels();
        isRealtimeTracking = true;
        startRealtimeTracking();
    }
})();
</script>

</body>
</html>