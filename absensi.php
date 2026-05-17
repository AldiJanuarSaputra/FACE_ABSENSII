<?php
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
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Absensi Face ID</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script defer src="js/face-api.min.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:Arial,sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#000000,#1a001a,#ff1493);
}

.container{
    width:480px;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,0.15);
    border-radius:20px;
    padding:30px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.5);
}

h1{color:#fff;font-size:26px;margin-bottom:6px;}
.subtitle{color:#ff9edb;font-size:13px;margin-bottom:20px;}

.video-wrap{
    position:relative;
    display:inline-block;
}

video{
    width:360px;height:270px;
    border-radius:15px;
    border:3px solid #ff4db8;
    background:#111;
    display:block;
}

canvas.overlay{
    position:absolute;
    top:0;left:0;
    width:360px;height:270px;
    border-radius:15px;
    pointer-events:none;
}

.btn-row{display:flex;gap:10px;margin-top:14px;}

button{
    flex:1;padding:14px;
    border:none;border-radius:12px;
    font-size:15px;font-weight:bold;color:#fff;
    cursor:pointer;
    background:linear-gradient(90deg,#ff1493,#ff69b4);
    transition:0.3s;
}
button:hover{transform:scale(1.03);box-shadow:0 0 15px #ff69b4;}
button:disabled{opacity:0.5;cursor:not-allowed;transform:none;}

.btn-reg{background:linear-gradient(90deg,#6a0dad,#9b30ff);}

#status{
    margin-top:14px;
    color:#fff;
    font-size:14px;
    min-height:20px;
    line-height:1.6;
}
.ok{color:#7fff7f;}
.err{color:#ff6b6b;}
.info{color:#ffe066;}

.btn-secondary{
    background: rgba(255,255,255,0.08) !important;
    border: 1px solid rgba(255,255,255,0.15) !important;
    margin-top: 10px;
    width: 100%;
    color: #fff;
    font-weight: bold;
    border-radius: 12px;
    padding: 14px;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-secondary:hover{
    background: rgba(255,255,255,0.15) !important;
    box-shadow: none !important;
    transform: scale(1.02);
}

/* Hasil absen box */
#hasil-box{
    display:none;
    margin-top:14px;
    background:rgba(0,0,0,0.35);
    border-radius:12px;
    padding:12px;
    font-size:14px;
    color:#fff;
    text-align:left;
    line-height:1.8;
}
#hasil-box.show{display:block;}
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>

<div class="container">
    <h1><i class="fa-solid fa-graduation-cap" style="color: #ff1493; margin-right: 8px;"></i>Absensi Face ID</h1>
    <p class="subtitle">Posisikan wajah di depan kamera, lalu tekan Scan</p>

    <div class="video-wrap">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="overlay" class="overlay"></canvas>
    </div>

    <div class="btn-row">
        <button id="btnScan" onclick="scan()" disabled><i class="fa-solid fa-camera" style="margin-right: 6px;"></i>Scan Wajah</button>
        <a href="register.php" style="flex:1;text-decoration:none;">
            <button class="btn-reg" type="button" style="width:100%;"><i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i>Daftar Wajah</button>
        </a>
    </div>
    <a href="index.php" style="text-decoration:none; display: block; width: 100%;">
        <button class="btn-secondary" type="button"><i class="fa-solid fa-house"></i>Kembali ke Menu Utama</button>
    </a>

    <p id="status" class="info">Memuat sistem...</p>

    <div id="hasil-box">
        <strong>Hasil Absensi:</strong><br>
        <span id="info-nama"></span><br>
        <span id="info-nis"></span><br>
        <span id="info-kelas"></span><br>
        <span id="info-waktu"></span><br>
        <span id="info-status"></span>
    </div>

    <!-- Mini Rekap Hari Ini -->
    <div id="mini-rekap-box" style="margin-top: 20px; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; padding: 18px; text-align: left;">
        <h3 style="font-size: 15px; color: #ff9edb; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; font-weight: 700;">
            <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Absensi Hari Ini
        </h3>
        
        <div style="max-height: 180px; overflow-y: auto; padding-right: 3px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.15); text-align: left;">
                        <th style="padding: 8px 5px; color: #bbb; font-weight: 600;">Nama</th>
                        <th style="padding: 8px 5px; color: #bbb; font-weight: 600;">Kelas</th>
                        <th style="padding: 8px 5px; color: #bbb; font-weight: 600; text-align: center; width: 80px;">Jam</th>
                        <th style="padding: 8px 5px; color: #bbb; font-weight: 600; text-align: center; width: 90px;">Status</th>
                    </tr>
                </thead>
                <tbody id="body-mini-rekap">
                    <?php if (count($riwayatHariIni) > 0): ?>
                        <?php foreach ($riwayatHariIni as $log): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 8px 5px; font-weight: 600;"><?php echo htmlspecialchars($log['nama']); ?></td>
                                <td style="padding: 8px 5px;"><?php echo htmlspecialchars($log['kelas']); ?></td>
                                <td style="padding: 8px 5px; text-align: center; color: #ff9edb; font-family: monospace; font-size: 13px;"><?php echo htmlspecialchars($log['jam']); ?></td>
                                <td style="padding: 8px 5px; text-align: center;">
                                    <span class="badge <?php echo $log['status'] === 'Hadir' ? 'badge-hadir' : 'badge-lambat'; ?>" style="padding: 3px 8px; font-size: 10px; border-radius: 6px;">
                                        <?php echo htmlspecialchars($log['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="empty-mini-row">
                            <td colspan="4" style="padding: 20px; text-align: center; color: #aaa;">
                                Belum ada siswa yang melakukan absensi hari ini.
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
    }catch(e){
        setStatus("❌ Gagal memuat data siswa: "+e.message,"err");
        btnScan.disabled = false;
        console.error(e);
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
}

// ── Init ───────────────────────────────────────────────
(async()=>{
    const camOk = await startCamera();
    if(camOk) await loadModels();
})();
</script>

</body>
</html>
