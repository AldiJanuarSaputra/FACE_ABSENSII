<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Wajah – Face Absensi</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#ff1493">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script defer src="js/face-api.min.js"></script>
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
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-dark: #070008;
    --neon-pink: #ff1493;
    --neon-pink-glow: rgba(255, 20, 147, 0.4);
    --neon-purple: #8b00ff;
    --neon-purple-glow: rgba(139, 0, 255, 0.4);
    --glass-bg: rgba(255, 255, 255, 0.03);
    --glass-border: rgba(255, 255, 255, 0.08);
    --text-primary: #ffffff;
    --text-secondary: #ffc4e8;
    --success: #00ff66;
    --danger: #ff3b30;
    --warning: #ffcc00;
}

body {
    font-family: 'Outfit', sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: radial-gradient(circle at center, #1b0022 0%, #050006 100%);
    overflow-x: hidden;
    color: var(--text-primary);
    padding: 15px;
}

/* Mobile-First Layout container */
.container {
    width: 100%;
    max-width: 440px;
    background: var(--glass-bg);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 24px;
    text-align: center;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6),
                inset 0 1px 1px rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}

/* Container Glowing Neon Outline Effects */
.container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 20, 147, 0.08) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

/* Header typography */
h2 {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-bottom: 20px;
    color: var(--text-primary);
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
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
    border: 1px solid var(--glass-border);
    outline: none;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.04);
    color: #fff;
    font-size: 14.5px;
    font-family: 'Outfit', sans-serif;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

input:focus {
    border-color: var(--neon-pink);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 10px rgba(255, 20, 147, 0.25);
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
    padding: 6px;
    background: linear-gradient(135deg, var(--neon-pink), var(--neon-purple));
    box-shadow: 0 0 25px rgba(139, 0, 255, 0.25);
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
    top: 6px;
    left: 6px;
    width: calc(100% - 12px);
    height: calc(100% - 12px);
    border-radius: 14px;
    pointer-events: none;
}

/* Sweeping Scanning Line */
.video-wrap::after {
    content: '';
    position: absolute;
    top: 6px;
    left: 6px;
    width: calc(100% - 12px);
    height: 3px;
    background: linear-gradient(to right, transparent, var(--neon-pink), #ffffff, var(--neon-pink), transparent);
    animation: scan 3s linear infinite;
    box-shadow: 0 0 12px var(--neon-pink);
    pointer-events: none;
    z-index: 10;
}

@keyframes scan {
    0% { top: 6px; }
    50% { top: calc(100% - 9px); }
    100% { top: 6px; }
}

/* Button & Controls Redesign */
button {
    width: 100%;
    padding: 14px 16px;
    border: none;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    background: linear-gradient(90deg, var(--neon-pink), #ff48a5);
    box-shadow: 0 4px 15px rgba(255, 20, 147, 0.35);
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
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 20, 147, 0.5);
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
    background: linear-gradient(90deg, var(--neon-purple), #a448ff) !important;
    box-shadow: 0 4px 15px rgba(139, 0, 255, 0.35) !important;
    margin-top: 10px;
    color: #fff;
    font-weight: 700;
    border-radius: 16px;
    padding: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.btn-secondary:hover {
    box-shadow: 0 6px 20px rgba(139, 0, 255, 0.5) !important;
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

.ok { color: var(--success); text-shadow: 0 0 10px rgba(0, 255, 102, 0.2); }
.err { color: var(--danger); text-shadow: 0 0 10px rgba(255, 59, 48, 0.2); }
.info { color: #ffe066; text-shadow: 0 0 10px rgba(255, 224, 102, 0.2); }

/* Splash Screen Redesign */
.splash-screen {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: radial-gradient(circle at center, #1b0022 0%, #050006 100%);
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
    color: var(--neon-pink);
    text-shadow: 0 0 35px var(--neon-pink-glow);
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
    text-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
}
.splash-spinner {
    width: 48px;
    height: 48px;
    border: 3.5px solid rgba(255, 20, 147, 0.1);
    border-radius: 50%;
    border-top-color: var(--neon-pink);
    animation: spin 1s cubic-bezier(0.55, 0.085, 0.68, 0.53) infinite;
    margin: 0 auto 24px;
    box-shadow: 0 0 15px rgba(255, 20, 147, 0.2);
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
    50% { transform: scale(1.08); opacity: 1; text-shadow: 0 0 45px rgba(255, 20, 147, 0.8); }
}
</style>
</head>
<body>

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
    <h2><i class="fa-solid fa-user-plus" style="color: var(--neon-pink)"></i>Registrasi Wajah</h2>

    <input type="text" id="nis"   placeholder="NIS Siswa">
    <input type="text" id="nama"  placeholder="Nama Lengkap">
    <input type="text" id="kelas" placeholder="Kelas (contoh: X-IPA-1)">

    <div class="video-wrap">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="overlay" class="overlay"></canvas>
    </div>

    <canvas id="canvas" style="display:none;" width="320" height="240"></canvas>

    <button id="btnDaftar" onclick="daftar()" disabled><i class="fa-solid fa-cloud-arrow-up"></i>Daftar Sekarang</button>
    <a href="absensi.php" style="text-decoration:none;"><button class="btn-secondary" type="button"><i class="fa-solid fa-camera-retro"></i>Ke Halaman Absensi</button></a>

    <p id="hasil" class="info">Memuat sistem...</p>
</div>

<script>
const video    = document.getElementById("video");
const canvas   = document.getElementById("canvas");
const overlay  = document.getElementById("overlay");
const hasil    = document.getElementById("hasil");
const btnDaftar = document.getElementById("btnDaftar");

let modelsReady = false;

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
    const nis   = document.getElementById("nis").value.trim();
    const nama  = document.getElementById("nama").value.trim();
    const kelas = document.getElementById("kelas").value.trim();

    if(!nis || !nama || !kelas){
        setStatus("⚠️ Lengkapi semua data terlebih dahulu","err");
        return;
    }
    if(!modelsReady){
        setStatus("⚠️ Model AI belum siap, tunggu sebentar","info");
        return;
    }

    btnDaftar.disabled = true;
    setStatus("🔍 Mendeteksi wajah...","info");

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize:416,scoreThreshold:0.3}))
        .withFaceLandmarks()
        .withFaceDescriptor();

    if(!detection){
        setStatus("❌ Wajah tidak terdeteksi, pastikan wajah terlihat jelas","err");
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

    fetch("simpan_siswa.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({nis, nama, kelas, wajah:foto, descriptor})
    })
    .then(r=>r.text())
    .then(msg=>{
        if(msg.includes("berhasil")){
            setStatus("✅ "+msg,"ok");
            // Reset form
            document.getElementById("nis").value  = "";
            document.getElementById("nama").value = "";
            document.getElementById("kelas").value= "";
            // Bersihkan overlay setelah 2 detik
            setTimeout(()=>overlay.getContext("2d").clearRect(0,0,overlay.width,overlay.height),2000);
        }else{
            setStatus("❌ "+msg,"err");
        }
        btnDaftar.disabled = false;
    })
    .catch(()=>{
        setStatus("❌ Gagal menghubungi server","err");
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

// ── Init ───────────────────────────────────────────────
(async()=>{
    const camOk = await startCamera();
    if(camOk) await loadModels();
})();
</script>

</body>
</html>