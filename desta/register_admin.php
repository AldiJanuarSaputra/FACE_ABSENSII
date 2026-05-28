<?php
session_start();
include "../config/koneksi.php";

$pesan = "";
$tipePesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $role     = trim($_POST['role'] ?? 'admin');

    if (empty($username) || empty($password) || empty($nama)) {
        $pesan = "Semua kolom harus diisi!";
        $tipePesan = "err";
    } elseif (!in_array($role, ['admin', 'guru'])) {
        $pesan = "Peran tidak valid!";
        $tipePesan = "err";
    } else {
        try {
            // Cek apakah username sudah ada
            $cek = $koneksi->prepare("SELECT COUNT(*) FROM admin WHERE username = :username");
            $cek->execute([':username' => $username]);
            if ($cek->fetchColumn() > 0) {
                $pesan = "Username '$username' sudah terdaftar!";
                $tipePesan = "err";
            } else {
                // Hashing password
                $hashedPass = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $koneksi->prepare("INSERT INTO admin (username, password, nama, role) VALUES (:username, :password, :nama, :role)");
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPass,
                    ':nama'     => $nama,
                    ':role'     => $role
                ]);
                $pesan = "Registrasi Akun " . ucfirst($role) . " berhasil! Silakan login.";
                $tipePesan = "ok";
            }
        } catch (PDOException $e) {
            $pesan = "Gagal mendaftar: " . $e->getMessage();
            $tipePesan = "err";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun Admin/Guru – Face Absensi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
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
    --bg-dark: #090f1d;
    --bg-gradient: radial-gradient(circle at top, #1e1b4b 0%, #090f1d 100%);
    --card-bg: rgba(15, 23, 42, 0.45);
    --card-border: rgba(255, 255, 255, 0.05);
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --primary-glow: rgba(99, 102, 241, 0.2);
    --secondary: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
    --text-primary: #f8fafc;
    --text-secondary: #94a3b8;
}

html[data-theme="light"] {
    --bg-dark: #f8fafc;
    --bg-gradient: radial-gradient(circle at top, #e0e7ff 0%, #f8fafc 100%);
    --card-bg: rgba(255, 255, 255, 0.7);
    --card-border: rgba(99, 102, 241, 0.08);
    --primary: #4f46e5;
    --primary-hover: #4338ca;
    --primary-glow: rgba(79, 70, 229, 0.15);
    --secondary: #0ea5e9;
    --success: #10b981;
    --danger: #ef4444;
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
    overflow-x: hidden;
    color: var(--text-primary);
    padding: 20px;
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
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4),
                inset 0 1px 1px rgba(255, 255, 255, 0.05);
    position: relative;
    z-index: 10;
}

h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 26px;
    font-weight: 800;
    text-align: center;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.subtitle {
    text-align: center;
    color: var(--text-secondary);
    font-size: 13.5px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
    text-align: left;
}

label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-bottom: 6px;
    padding-left: 2px;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    font-size: 14px;
}

input, select {
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1px solid var(--card-border);
    outline: none;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
    font-size: 14px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.3s ease;
}

input:focus, select:focus {
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.06);
    box-shadow: 0 0 12px var(--primary-glow);
}

select {
    padding-left: 40px;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
}

.input-wrapper::after {
    content: '\f0d7';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    pointer-events: none;
}

.input-wrapper.no-arrow::after {
    display: none;
}

button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 16px;
    font-size: 14px;
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
    margin-top: 25px;
}

button:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

button:active {
    transform: translateY(1px);
}

.footer-link {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: var(--text-secondary);
}

.footer-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.footer-link a:hover {
    text-decoration: underline;
}

.alert {
    padding: 12px 16px;
    border-radius: 14px;
    font-size: 13.5px;
    font-weight: 500;
    margin-bottom: 20px;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease-out;
}

.alert-success {
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.alert-danger {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: var(--danger);
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<div class="container">
    <h2>Daftar Akun Baru</h2>
    <p class="subtitle">Registrasi akun khusus Admin / Guru Absensi</p>

    <?php if (!empty($pesan)): ?>
        <div class="alert <?php echo $tipePesan === 'ok' ? 'alert-success' : 'alert-danger'; ?>">
            <i class="fa-solid <?php echo $tipePesan === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
            <span><?php echo htmlspecialchars($pesan); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="../desta/register_admin.php">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <div class="input-wrapper no-arrow">
                <i class="fa-solid fa-signature"></i>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda..." required>
            </div>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper no-arrow">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="username" name="username" placeholder="Buat username unik..." required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi (Password)</label>
            <div class="input-wrapper no-arrow">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Buat kata sandi..." required>
            </div>
        </div>

        <div class="form-group">
            <label for="role">Pilih Peran (Role)</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user-shield"></i>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                </select>
            </div>
        </div>

        <button type="submit"><i class="fa-solid fa-user-plus"></i>Daftar Sekarang</button>
    </form>

    <div class="footer-link">
        Sudah memiliki akun? <a href="../desta/login.php">Masuk disini</a>
    </div>
</div>

</body>
</html>
