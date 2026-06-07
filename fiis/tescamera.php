<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tes Kamera Premium – Face Absensi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Theme Initializer
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
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
            --card-bg: #111422;
            --card-border: #374151;
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
            --card-border: #cbd5e1;
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

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--bg-gradient);
            color: var(--text-primary);
            padding: 15px;
        }

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
        }

        h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 13.5px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .video-wrap {
            position: relative;
            display: inline-block;
            border-radius: 20px;
            overflow: hidden;
            padding: 5px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15);
            margin-bottom: 20px;
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

        .status-box {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 16px;
            text-align: left;
            margin-bottom: 20px;
            font-size: 13.5px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .status-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .status-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .status-val {
            font-weight: 700;
        }

        .val-ok { color: var(--success); }
        .val-err { color: var(--danger); }
        .val-pending { color: var(--warning); }

        .btn-action {
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
            text-decoration: none;
            margin-bottom: 10px;
        }

        .btn-action:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            border: 2px solid var(--card-border);
            color: var(--text-primary);
            box-shadow: none;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: none;
        }

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
            transition: all 0.3s ease;
            z-index: 100;
        }

        .theme-toggle-floating:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>

<button class="theme-toggle-floating" onclick="toggleTheme()" id="themeBtn" aria-label="Toggle Theme">
    <i class="fa-solid fa-moon"></i>
</button>

<div class="container">
    <h2><i class="fa-solid fa-camera" style="color: var(--primary)"></i>Tes Kamera Hardware</h2>
    <p class="subtitle">Verifikasi fungsionalitas kamera Anda sebelum melakukan Face ID</p>

    <div class="video-wrap">
        <video id="video" autoplay playsinline muted></video>
    </div>

    <div class="status-box">
        <div class="status-item">
            <span class="status-label">Akses Kamera:</span>
            <span id="camAccess" class="status-val val-pending">⏳ Mengecek...</span>
        </div>
        <div class="status-item">
            <span class="status-label">Resolusi Input:</span>
            <span id="camResolution" class="status-val">⏳ Mengecek...</span>
        </div>
        <div class="status-item">
            <span class="status-label">Stream Status:</span>
            <span id="streamStatus" class="status-val val-pending">Non-Aktif</span>
        </div>
    </div>

    <button id="btnRetry" class="btn-action" onclick="initCamera()" style="display:none;"><i class="fa-solid fa-rotate-right"></i> Coba Akses Kamera Lagi</button>
    <a href="./absensi.php" class="btn-action"><i class="fa-solid fa-camera-retro"></i> Ke Halaman Absensi</a>
    <a href="./register.php" class="btn-action btn-secondary"><i class="fa-solid fa-user-plus"></i> Ke Registrasi Wajah</a>
</div>

<script>
    const video = document.getElementById("video");
    const camAccess = document.getElementById("camAccess");
    const camResolution = document.getElementById("camResolution");
    const streamStatus = document.getElementById("streamStatus");
    const btnRetry = document.getElementById("btnRetry");

    async function initCamera() {
        camAccess.textContent = "⏳ Meminta izin...";
        camAccess.className = "status-val val-pending";
        streamStatus.textContent = "⏳ Memulai...";
        streamStatus.className = "status-val val-pending";
        btnRetry.style.display = "none";

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480, facingMode: "user" },
                audio: false
            });
            
            video.srcObject = stream;
            
            camAccess.textContent = "✅ Diizinkan";
            camAccess.className = "status-val val-ok";
            
            streamStatus.textContent = "🟢 Aktif (Berjalan)";
            streamStatus.className = "status-val val-ok";

            // Deteksi resolusi setelah metadata video terisi
            video.onloadedmetadata = () => {
                camResolution.textContent = `${video.videoWidth} x ${video.videoHeight}`;
            };

        } catch (err) {
            console.error("Gagal mengakses kamera:", err);
            camAccess.textContent = "❌ Ditolak / Tidak Ditemukan";
            camAccess.className = "status-val val-err";
            streamStatus.textContent = "🔴 Gagal";
            streamStatus.className = "status-val val-err";
            camResolution.textContent = "Tidak Tersedia";
            btnRetry.style.display = "block";
        }
    }

    // Theme Switcher
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
        themeBtn.innerHTML = theme === 'light' 
            ? '<i class="fa-solid fa-sun" style="color: #f59e0b;"></i>' 
            : '<i class="fa-solid fa-moon"></i>';
    }

    updateThemeUI(savedTheme);

    // Jalankan inisialisasi
    initCamera();
</script>
</body>
</html>