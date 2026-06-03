<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Wajah Siswa – Face Absensi</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script defer src="js/face-api.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Outfit',sans-serif;}
body{min-height:100vh;background:linear-gradient(135deg,#000000,#130113,#240024);color:#fff;padding:30px 20px;}
.wrapper{max-width:960px;margin:0 auto;}

header{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:20px;}
h1{font-size:24px;font-weight:700;} h1 span{color:#ff1493;}
.btn-back{padding:10px 18px;border-radius:10px;color:#fff;text-decoration:none;font-size:14px;font-weight:600;transition:.3s;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.08);}
.btn-back:hover{background:rgba(255,255,255,.18);}

/* Layout */
.main-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;}
@media(max-width:700px){.main-grid{grid-template-columns:1fr;}}

/* Card */
.card{background:rgba(255,255,255,.04);backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:26px;}
.card-title{font-size:15px;font-weight:700;color:#ff9edb;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
.card-title i{width:32px;height:32px;border-radius:8px;background:rgba(255,20,147,.2);color:#ff1493;display:flex;align-items:center;justify-content:center;font-size:14px;}

/* Form */
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:12px;color:#bbb;margin-bottom:7px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.form-group input,.form-group select{width:100%;padding:12px 14px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#fff;font-size:14px;outline:none;transition:.3s;font-family:'Outfit',sans-serif;}
.form-group input:focus,.form-group select:focus{border-color:#ff1493;box-shadow:0 0 0 3px rgba(255,20,147,.15);}
.form-group select option{background:#1a001a;color:#fff;}
.form-group input::placeholder{color:#666;}

/* Kamera */
.video-wrap{position:relative;width:100%;aspect-ratio:4/3;border-radius:14px;overflow:hidden;background:#0a000a;border:2px solid rgba(255,20,147,.4);}
video{width:100%;height:100%;object-fit:cover;display:block;}
canvas.overlay{position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;}
.video-status{position:absolute;bottom:10px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.7);padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;color:#fff;backdrop-filter:blur(4px);}
canvas#snapshot{display:none;}

/* Sample Wajah */
.sample-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:16px;}
.sample-slot{aspect-ratio:1;border-radius:10px;border:2px dashed rgba(255,255,255,.2);background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;font-size:11px;color:#666;overflow:hidden;position:relative;transition:.3s;}
.sample-slot img{width:100%;height:100%;object-fit:cover;}
.sample-slot.captured{border-color:#7fff7f;border-style:solid;}
.sample-slot .num{position:absolute;top:3px;left:5px;font-size:10px;font-weight:700;color:#fff;text-shadow:0 0 4px #000;}
.progress-bar{height:6px;background:rgba(255,255,255,.1);border-radius:10px;overflow:hidden;margin-bottom:8px;}
.progress-fill{height:100%;background:linear-gradient(90deg,#ff1493,#ff69b4);border-radius:10px;transition:.4s;}
.progress-label{font-size:12px;color:#aaa;margin-bottom:16px;}

/* Buttons */
.btn-capture{width:100%;padding:13px;background:linear-gradient(90deg,#ff1493,#ff69b4);border:none;border-radius:12px;color:#fff;font-size:15px;font-weight:700;cursor:pointer;transition:.3s;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-capture:hover:not(:disabled){box-shadow:0 0 20px #ff69b4;transform:translateY(-1px);}
.btn-capture:disabled{opacity:.45;cursor:not-allowed;transform:none;}
.btn-simpan{width:100%;padding:13px;background:linear-gradient(90deg,#00bfff,#0080ff);border:none;border-radius:12px;color:#fff;font-size:15px;font-weight:700;cursor:pointer;transition:.3s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:10px;}
.btn-simpan:hover:not(:disabled){box-shadow:0 0 20px rgba(0,191,255,.6);transform:translateY(-1px);}
.btn-simpan:disabled{opacity:.45;cursor:not-allowed;transform:none;}
.btn-ulang{width:100%;padding:10px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);border-radius:10px;color:#ccc;font-size:13px;cursor:pointer;transition:.3s;margin-top:8px;}
.btn-ulang:hover{background:rgba(255,255,255,.14);}

/* Status */
.status-box{padding:12px 16px;border-radius:10px;font-size:14px;display:flex;align-items:center;gap:10px;margin-top:12px;min-height:44px;}
.status-info{background:rgba(0,191,255,.1);border:1px solid rgba(0,191,255,.3);color:#00bfff;}
.status-ok{background:rgba(127,255,127,.1);border:1px solid rgba(127,255,127,.3);color:#7fff7f;}
.status-err{background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.3);color:#ff6b6b;}
.status-warn{background:rgba(255,200,0,.1);border:1px solid rgba(255,200,0,.3);color:#ffc800;}

/* Tips */
.tips-box{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:14px 16px;}
.tips-box ul{list-style:none;display:flex;flex-direction:column;gap:7px;}
.tips-box li{font-size:13px;color:#aaa;display:flex;align-items:flex-start;gap:8px;}
.tips-box li i{color:#ff1493;margin-top:2px;width:14px;}

@keyframes fadeIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<div class="wrapper">

  <header>
    <h1><i class="fa-solid fa-camera" style="color:#ff1493;margin-right:10px"></i>Registrasi <span>Wajah Siswa</span></h1>
    <a href="siswa.php" class="btn-back"><i class="fa-solid fa-arrow-left" style="margin-right:6px"></i>Kembali</a>
  </header>

  <div class="main-grid">
    <!-- Kolom Kiri: Form & Kamera -->
    <div>
      <!-- Form Detail Siswa -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fa-solid fa-id-card"></i> Data Siswa</div>
        <div class="form-group">
          <label>NIS Siswa</label>
          <input type="text" id="nis" placeholder="Masukkan NIS siswa...">
        </div>
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" id="nama" placeholder="Masukkan nama lengkap...">
        </div>
        <div class="form-group">
          <label>Kelas</label>
          <input type="text" id="kelas" placeholder="Contoh: X-IPA-1">
        </div>
      </div>

      <!-- Kamera -->
      <div class="card">
        <div class="card-title"><i class="fa-solid fa-video"></i> Live Kamera</div>
        <div class="video-wrap">
          <video id="video" autoplay muted playsinline></video>
          <canvas id="overlay" class="overlay"></canvas>
          <div class="video-status" id="videoStatus">Memuat...</div>
        </div>
        <canvas id="snapshot" width="320" height="240"></canvas>
      </div>
    </div>

    <!-- Kolom Kanan: Foto Sampel & Aksi -->
    <div>
      <!-- Foto Sampel -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fa-solid fa-images"></i> Foto Sampel Wajah (5 Foto)</div>

        <div class="sample-grid" id="sampleGrid">
          <?php for ($i=1;$i<=5;$i++): ?>
          <div class="sample-slot" id="slot<?= $i ?>">
            <span class="num"><?= $i ?></span>
            <i class="fa-solid fa-camera" style="color:#444;font-size:18px"></i>
          </div>
          <?php endfor; ?>
        </div>

        <div class="progress-bar">
          <div class="progress-fill" id="progressFill" style="width:0%"></div>
        </div>
        <p class="progress-label" id="progressLabel">0 / 5 foto diambil</p>

        <button class="btn-capture" id="btnCapture" disabled onclick="ambilFoto()">
          <i class="fa-solid fa-camera"></i> Ambil Foto (<span id="capCount">0</span>/5)
        </button>
        <button class="btn-ulang" onclick="ulangFoto()">
          <i class="fa-solid fa-rotate-left" style="margin-right:6px"></i>Ulangi Semua Foto
        </button>
      </div>

      <!-- Simpan -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fa-solid fa-floppy-disk"></i> Simpan Registrasi</div>
        <button class="btn-simpan" id="btnSimpan" disabled onclick="simpanData()">
          <i class="fa-solid fa-floppy-disk"></i> Simpan & Daftarkan Wajah
        </button>
        <div id="statusBox" class="status-box status-info">
          <i class="fa-solid fa-circle-info"></i>
          <span id="statusMsg">Memuat model AI, harap tunggu...</span>
        </div>
      </div>

      <!-- Tips -->
      <div class="card">
        <div class="card-title"><i class="fa-solid fa-lightbulb"></i> Tips Pengambilan Foto</div>
        <div class="tips-box">
          <ul>
            <li><i class="fa-solid fa-check"></i>Pastikan wajah terlihat jelas dan pencahayaan cukup</li>
            <li><i class="fa-solid fa-check"></i>Ambil foto dari berbagai sudut (depan, kiri, kanan)</li>
            <li><i class="fa-solid fa-check"></i>Hindari penutup wajah seperti masker atau kacamata gelap</li>
            <li><i class="fa-solid fa-check"></i>Jaga jarak 30–60 cm dari kamera</li>
            <li><i class="fa-solid fa-check"></i>Tunggu kotak deteksi hijau sebelum klik Ambil Foto</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const video      = document.getElementById('video');
const overlay    = document.getElementById('overlay');
const snapshot   = document.getElementById('snapshot');
const btnCapture = document.getElementById('btnCapture');
const btnSimpan  = document.getElementById('btnSimpan');
const capCount   = document.getElementById('capCount');
const progressFill  = document.getElementById('progressFill');
const progressLabel = document.getElementById('progressLabel');
const videoStatus   = document.getElementById('videoStatus');

const MAX_FOTO = 5;
let sampelFoto = [];        // array base64 images
let sampelDesc = [];        // array descriptor 128-float
let modelsReady = false;

// ── Status Helper ────────────────────────────────
function setStatus(msg, type='info') {
    const box = document.getElementById('statusBox');
    const icons = {info:'fa-circle-info', ok:'fa-circle-check', err:'fa-circle-exclamation', warn:'fa-triangle-exclamation'};
    box.className = 'status-box status-' + type;
    document.getElementById('statusMsg').textContent = msg;
    box.querySelector('i').className = 'fa-solid ' + (icons[type]||icons.info);
}

// ── Update Progress ──────────────────────────────
function updateProgress() {
    const n = sampelFoto.length;
    progressFill.style.width = (n / MAX_FOTO * 100) + '%';
    progressLabel.textContent = n + ' / ' + MAX_FOTO + ' foto diambil';
    capCount.textContent = n;
    btnSimpan.disabled = n < MAX_FOTO;
    btnCapture.disabled = !modelsReady || n >= MAX_FOTO;
    if (n >= MAX_FOTO) {
        setStatus('✅ ' + MAX_FOTO + ' foto berhasil diambil! Klik Simpan untuk mendaftarkan.', 'ok');
    }
}

// ── Kamera ──────────────────────────────────────
async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({video:{width:640,height:480,facingMode:'user'},audio:false});
        video.srcObject = stream;
        videoStatus.textContent = 'Kamera aktif';
        return true;
    } catch(e) {
        videoStatus.textContent = 'Kamera gagal';
        setStatus('❌ Kamera tidak bisa diakses: ' + e.message, 'err');
        return false;
    }
}

// ── Load Model ───────────────────────────────────
async function loadModels() {
    try {
        setStatus('⏳ Memuat model AI face-api.js...', 'info');
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('models')
        ]);
        modelsReady = true;
        btnCapture.disabled = false;
        setStatus('✅ Model siap! Isi data siswa lalu ambil 5 foto wajah.', 'ok');
        videoStatus.textContent = '🟢 Siap';
        startDetectionLoop();
    } catch(e) {
        setStatus('❌ Gagal memuat model: ' + e.message, 'err');
    }
}

// ── Detection Loop (Live Box) ────────────────────
async function startDetectionLoop() {
    const ctx = overlay.getContext('2d');
    setInterval(async () => {
        if (!modelsReady || video.paused) return;
        overlay.width  = video.videoWidth  || 640;
        overlay.height = video.videoHeight || 480;
        ctx.clearRect(0,0,overlay.width,overlay.height);
        const det = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize:320,scoreThreshold:0.4}));
        if (det) {
            const dims = {width:overlay.width, height:overlay.height};
            const resized = faceapi.resizeResults(det, dims);
            faceapi.draw.drawDetections(overlay, resized);
            videoStatus.textContent = '🟢 Wajah terdeteksi';
        } else {
            videoStatus.textContent = '🔴 Wajah tidak terdeteksi';
        }
    }, 400);
}

// ── Ambil Foto Sampel ────────────────────────────
async function ambilFoto() {
    if (sampelFoto.length >= MAX_FOTO || !modelsReady) return;
    btnCapture.disabled = true;
    setStatus('🔍 Mendeteksi wajah...', 'info');

    const det = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({inputSize:416, scoreThreshold:0.35}))
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!det) {
        setStatus('❌ Wajah tidak terdeteksi. Pastikan wajah terlihat jelas!', 'err');
        btnCapture.disabled = false;
        return;
    }

    // Ambil snapshot
    const ctx = snapshot.getContext('2d');
    snapshot.width = 320; snapshot.height = 240;
    ctx.drawImage(video, 0, 0, 320, 240);
    const foto = snapshot.toDataURL('image/jpeg', 0.75);

    const idx = sampelFoto.length;
    sampelFoto.push(foto);
    sampelDesc.push(Array.from(det.descriptor));

    // Tampilkan di slot
    const slot = document.getElementById('slot' + (idx+1));
    slot.innerHTML = '<span class="num">' + (idx+1) + '</span><img src="' + foto + '">';
    slot.classList.add('captured');

    updateProgress();
    if (sampelFoto.length < MAX_FOTO) {
        setStatus('📸 Foto ' + sampelFoto.length + ' diambil! Ubah posisi wajah sedikit lalu ambil lagi.', 'ok');
        setTimeout(() => { btnCapture.disabled = false; }, 800);
    }
}

// ── Ulangi Foto ──────────────────────────────────
function ulangFoto() {
    sampelFoto = []; sampelDesc = [];
    for (let i=1; i<=MAX_FOTO; i++) {
        const slot = document.getElementById('slot'+i);
        slot.innerHTML = '<span class="num">'+i+'</span><i class="fa-solid fa-camera" style="color:#444;font-size:18px"></i>';
        slot.classList.remove('captured');
    }
    updateProgress();
    btnCapture.disabled = !modelsReady;
    setStatus('🔄 Foto direset. Silakan ambil ulang 5 foto wajah.', 'warn');
}

// ── Simpan Data ──────────────────────────────────
async function simpanData() {
    const nis   = document.getElementById('nis').value.trim();
    const nama  = document.getElementById('nama').value.trim();
    const kelas = document.getElementById('kelas').value.trim();

    if (!nis || !nama || !kelas) {
        setStatus('⚠️ Lengkapi data siswa terlebih dahulu (NIS, Nama, Kelas)!', 'warn');
        return;
    }
    if (sampelFoto.length < MAX_FOTO) {
        setStatus('⚠️ Harap ambil 5 foto wajah terlebih dahulu!', 'warn');
        return;
    }

    btnSimpan.disabled = true;
    setStatus('💾 Menyimpan data & descriptor wajah...', 'info');

    // Gunakan foto pertama sebagai foto profil, hitung rata-rata descriptor
    const wajah = sampelFoto[0];
    const avgDescriptor = sampelDesc[0].map((_, i) =>
        sampelDesc.reduce((sum, d) => sum + d[i], 0) / sampelDesc.length
    );

    try {
        const res = await fetch('simpan_siswa.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({nis, nama, kelas, wajah, descriptor: avgDescriptor})
        });
        const msg = await res.text();
        if (msg.includes('berhasil')) {
            setStatus('✅ ' + msg, 'ok');
            document.getElementById('nis').value = '';
            document.getElementById('nama').value = '';
            document.getElementById('kelas').value = '';
            ulangFoto();
            setTimeout(() => setStatus('Siap untuk registrasi siswa berikutnya.', 'info'), 3000);
        } else {
            setStatus('❌ ' + msg, 'err');
            btnSimpan.disabled = false;
        }
    } catch(e) {
        setStatus('❌ Gagal menghubungi server: ' + e.message, 'err');
        btnSimpan.disabled = false;
    }
}

// ── Init ─────────────────────────────────────────
(async () => {
    const camOk = await startCamera();
    if (camOk) await loadModels();
})();
</script>
</body>
</html>