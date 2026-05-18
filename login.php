<?php
session_start();
include "koneksi.php";

$error = '';

// Jika sudah login, langsung arahkan ke dashboard masing-masing
if (isset($_SESSION['admin_user'])) {
    header("Location: index.php");
    exit;
} elseif (isset($_SESSION['siswa_user'])) {
    header("Location: siswa_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrNis = trim($_POST['username_nis']);
    $password      = trim($_POST['password']);
    $role          = $_POST['role'] ?? 'siswa';

    if (!$usernameOrNis || !$password) {
        $error = "Semua kolom login wajib diisi!";
    } else {
        try {
            if ($role === 'siswa') {
                // Query ke tabel siswa menggunakan NIS
                $stmt = $koneksi->prepare("SELECT id, nis, nama, kelas, password FROM siswa WHERE nis = :nis");
                $stmt->execute([':nis' => $usernameOrNis]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Validasi password (jika password belum diset di DB, izinkan setup/bantu info)
                    if (empty($user['password'])) {
                        $error = "Akun siswa Anda belum memiliki password. Silakan hubungi Admin untuk menyetel password Anda.";
                    } elseif (password_verify($password, $user['password'])) {
                        // Login sukses sebagai siswa
                        $_SESSION['siswa_user'] = [
                            'id'    => $user['id'],
                            'nis'   => $user['nis'],
                            'nama'  => $user['nama'],
                            'kelas' => $user['kelas']
                        ];
                        header("Location: siswa_dashboard.php");
                        exit;
                    } else {
                        $error = "NIS atau Password siswa salah!";
                    }
                } else {
                    $error = "NIS siswa tidak terdaftar!";
                }
            } else {
                // Query ke tabel admin (Role: Admin / Guru)
                $stmt = $koneksi->prepare("SELECT id, username, password, nama, role FROM admin WHERE username = :username");
                $stmt->execute([':username' => $usernameOrNis]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Login sukses sebagai admin/guru
                    $_SESSION['admin_user'] = [
                        'id'       => $user['id'],
                        'username' => $user['username'],
                        'nama'     => $user['nama'],
                        'role'     => $user['role']
                    ];
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Username atau Password Admin/Guru salah!";
                }
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Portal – Face Absensi</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#6366f1">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
    --danger: #ef4444;
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
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --danger: #ef4444;
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

.login-container {
    width: 100%;
    max-width: 420px;
    background: var(--card-bg);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid var(--card-border);
    border-radius: 24px;
    padding: 35px 30px;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4),
                inset 0 1px 1px rgba(255, 255, 255, 0.08);
    position: relative;
    overflow: hidden;
}

.brand-logo {
    font-size: 54px;
    color: var(--primary);
    margin-bottom: 15px;
    animation: float 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}

.subtitle {
    color: var(--text-secondary);
    font-size: 13.5px;
    margin-bottom: 30px;
}

/* Role Selection Switch */
.role-switch {
    display: flex;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--card-border);
    border-radius: 14px;
    padding: 4px;
    margin-bottom: 25px;
}

.role-option {
    flex: 1;
    position: relative;
}

.role-option input {
    position: absolute;
    opacity: 0;
    width: 0; height: 0;
}

.role-label {
    display: block;
    padding: 10px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-secondary);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.role-option input:checked + .role-label {
    color: #fff;
    background: var(--primary);
    box-shadow: 0 4px 12px var(--primary-glow);
}

/* Form Styling */
.form-group {
    text-align: left;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper i {
    position: absolute;
    left: 15px;
    color: var(--text-secondary);
    font-size: 15px;
}

.form-control {
    width: 100%;
    padding: 13px 15px 13px 44px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--card-border);
    border-radius: 14px;
    color: #fff;
    font-size: 14px;
    outline: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.07);
    box-shadow: 0 0 10px var(--primary-glow);
}

.alert-danger {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: var(--danger);
    padding: 12px 15px;
    border-radius: 12px;
    font-size: 13px;
    margin-bottom: 20px;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-login {
    width: 100%;
    padding: 14px;
    background: var(--primary);
    border: none;
    border-radius: 14px;
    color: #fff;
    font-weight: 700;
    font-size: 14.5px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    box-shadow: 0 4px 15px var(--primary-glow);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-login:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

.btn-login:active {
    transform: translateY(0);
}
</style>
</head>
<body>

<div class="login-container">
    <div class="brand-logo">
        <i class="fa-solid fa-face-viewfinder"></i>
    </div>
    <h2>Portal Masuk Cerdas</h2>
    <p class="subtitle">Sistem Absensi Kehadiran Face ID Kelompok</p>

    <?php if ($error !== ''): ?>
        <div class="alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <!-- Switch Role -->
        <div class="role-switch">
            <div class="role-option">
                <input type="radio" id="role-siswa" name="role" value="siswa" checked onclick="updateLabels('siswa')">
                <label for="role-siswa" class="role-label"><i class="fa-solid fa-user-graduate" style="margin-right: 6px;"></i>Siswa</label>
            </div>
            <div class="role-option">
                <input type="radio" id="role-admin" name="role" value="admin" onclick="updateLabels('admin')">
                <label for="role-admin" class="role-label"><i class="fa-solid fa-user-shield" style="margin-right: 6px;"></i>Admin / Guru</label>
            </div>
        </div>

        <!-- Input Identitas -->
        <div class="form-group">
            <label for="username_nis" id="identity-label">Nomor Induk Siswa (NIS)</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-id-card" id="identity-icon"></i>
                <input type="text" id="username_nis" name="username_nis" class="form-control" placeholder="Masukkan NIS Anda..." required autocomplete="username">
            </div>
        </div>

        <!-- Input Password -->
        <div class="form-group">
            <label for="password">Kata Sandi (Password)</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password..." required autocomplete="current-password">
            </div>
        </div>

        <button type="submit" class="btn-login">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span>Masuk ke Sistem</span>
        </button>
    </form>
    
    <div style="margin-top: 20px; font-size: 13px; color: var(--text-secondary);">
        Belum terdaftar? 
        <a href="register.php" id="register-link" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: all 0.3s ease; margin-left: 4px;">Daftarkan Wajah (Face ID) Siswa</a>
    </div>
</div>

<script>
function updateLabels(role) {
    const label = document.getElementById('identity-label');
    const icon  = document.getElementById('identity-icon');
    const input = document.getElementById('username_nis');
    const regLink = document.getElementById('register-link');

    if (role === 'siswa') {
        label.textContent = "Nomor Induk Siswa (NIS)";
        icon.className = "fa-solid fa-id-card";
        input.placeholder = "Masukkan NIS Anda...";
        regLink.href = "register.php";
        regLink.innerHTML = "Daftarkan Wajah (Face ID) Siswa";
    } else {
        label.textContent = "Username Admin / Guru";
        icon.className = "fa-solid fa-user";
        input.placeholder = "Masukkan username...";
        regLink.href = "register_admin.php";
        regLink.innerHTML = "Daftar Akun Admin / Guru Baru";
    }
}
</script>
</body>
</html>
