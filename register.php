<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Wajah – Face Absensi</title>
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
    width:440px;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,0.15);
    border-radius:20px;
    padding:30px;
    box-shadow:0 10px 25px rgba(0,0,0,0.4);
    text-align:center;
}

h2{color:#fff;margin-bottom:20px;font-size:26px;}

input{
    width:100%;padding:12px;margin:6px 0;
    border:none;outline:none;border-radius:10px;
    background:rgba(255,255,255,0.12);color:#fff;font-size:15px;
}
input::placeholder{color:#ddd;}

.video-wrap{
    position:relative;
    display:inline-block;
    margin-top:15px;
}

video{
    width:320px;height:240px;
    border-radius:15px;
    border:3px solid #ff4db8;
    background:#222;
    display:block;
}

canvas.overlay{
    position:absolute;
    top:0;left:0;
    width:320px;height:240px;
    border-radius:15px;
    pointer-events:none;
}

button{
    width:100%;padding:14px;margin-top:15px;
    border:none;border-radius:12px;
    font-size:16px;font-weight:bold;color:#fff;cursor:pointer;
    background:linear-gradient(90deg,#ff1493,#ff69b4);
    transition:0.3s;
}
button:hover{transform:scale(1.03);box-shadow:0 0 15px #ff69b4;}
button:disabled{opacity:0.5;cursor:not-allowed;transform:none;}

.btn-secondary{
    background:linear-gradient(90deg,#6a0dad,#9b30ff);
    margin-top:8px;
}

#hasil{
    margin-top:15px;color:#fff;font-size:14px;
    min-height:20px;
}
.ok{color:#7fff7f;}
.err{color:#ff6b6b;}
.info{color:#ffe066;}
</style>
</head>
<body>

<div class="container">
    <h2><i class="fa-solid fa-user-plus" style="color: #ff1493; margin-right: 10px;"></i>Registrasi Wajah</h2>

    <input type="text" id="nis"   placeholder="NIS Siswa">
    <input type="text" id="nama"  placeholder="Nama Lengkap">
    <input type="text" id="kelas" placeholder="Kelas (contoh: X-IPA-1)">

    <div class="video-wrap">
        <video id="video" autoplay muted playsinline></video>
        <canvas id="overlay" class="overlay"></canvas>
    </div>

    <canvas id="canvas" style="display:none;" width="320" height="240"></canvas>

    <button id="btnDaftar" onclick="daftar()" disabled>Daftar Sekarang</button>
    <a href="absensi.php"><button class="btn-secondary" type="button"><i class="fa-solid fa-house" style="margin-right: 6px;"></i>Ke Halaman Absensi</button></a>

    <p id="hasil" class="info">Memuat sistem...</p>
</div>

<script>
const video    = document.getElementById("video");
const canvas   = document.getElementById("canvas");
const overlay  = document.getElementById("overlay");
const hasil    = document.getElementById("hasil");
const btnDaftar = document.getElementById("btnDaftar");

let modelsReady = false;

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
    }catch(e){
        setStatus("❌ Model gagal dimuat: "+e.message,"err");
        console.error(e);
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
}

// ── Init ───────────────────────────────────────────────
(async()=>{
    const camOk = await startCamera();
    if(camOk) await loadModels();
})();
</script>

</body>
</html>