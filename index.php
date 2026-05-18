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
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:Arial,sans-serif;min-height:100vh;
    display:flex;justify-content:center;align-items:center;
    background:linear-gradient(135deg,#000000,#1a001a,#ff1493);
}
.container{
    width:440px;background:rgba(255,255,255,0.08);
    backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.15);
    border-radius:20px;padding:30px;text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.5);
}
h1{color:#fff;font-size:26px;margin-bottom:8px;}
p.sub{color:#ff9edb;font-size:13px;margin-bottom:20px;}

.nav-links{display:flex;gap:10px;margin-bottom:20px;}
.nav-links a{
    flex:1;padding:12px;border-radius:12px;text-decoration:none;
    font-weight:bold;font-size:14px;color:#fff;
    background:linear-gradient(90deg,#ff1493,#ff69b4);
    transition:0.3s;
}
.nav-links a:hover{transform:scale(1.03);box-shadow:0 0 12px #ff69b4;}
.nav-links a.sec{background:linear-gradient(90deg,#6a0dad,#9b30ff);}
</style>
</head>
<body>
<div class="container">
    <h1><i class="fa-solid fa-graduation-cap" style="color: #ff1493; margin-right: 8px;"></i>Sistem Absensi Face ID</h1>
    <p class="sub">Pilih menu di bawah ini</p>

    <div class="nav-links">
        <a href="absensi.php"><i class="fa-solid fa-camera" style="margin-right: 6px;"></i>Scan Absensi</a>
        <a href="register.php" class="sec"><i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i>Daftar Wajah</a>
    </div>
    <a href="siswa.php" style="display:block;width:100%;padding:14px;border-radius:12px;text-decoration:none;font-weight:bold;font-size:15px;color:#fff;background:linear-gradient(90deg,#6a0dad,#9b30ff);transition:0.3s;margin-top:-10px;margin-bottom:12px;text-align:center;box-sizing:border-box;box-shadow: 0 4px 15px rgba(106, 13, 173, 0.2);"><i class="fa-solid fa-users-gear" style="margin-right: 8px;"></i>Kelola Data Siswa</a>
    <a href="rekap.php" style="display:block;width:100%;padding:14px;border-radius:12px;text-decoration:none;font-weight:bold;font-size:15px;color:#fff;background:linear-gradient(90deg,#00bfff,#1e90ff);transition:0.3s;margin-top:0px;margin-bottom:20px;text-align:center;box-sizing:border-box;box-shadow: 0 4px 15px rgba(0, 191, 255, 0.2);"><i class="fa-solid fa-chart-simple" style="margin-right: 8px;"></i>Rekapitulasi Absensi</a>

    <p style="color:#aaa;font-size:12px;">
        Scan Absensi: untuk pengenalan wajah siswa<br>
        Daftar Wajah: untuk mendaftarkan wajah baru
    </p>
</div>
</body>
</html>