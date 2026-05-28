<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.',
        'steps' => [['status' => 'error', 'message' => 'Akses ditolak: Sesi admin tidak valid.']]
    ]);
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'default';

require_once __DIR__ . '/../config/koneksi.php';

if (!isset($koneksi)) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.',
        'steps' => [['status' => 'error', 'message' => 'Gagal terhubung ke database. Cek file koneksi.php atau file .env Anda.']]
    ]);
    exit;
}

$steps = [];
$success = true;

if ($type === 'columns') {
    $steps[] = ['status' => 'info', 'message' => 'Memulai Migrasi Kolom tambahan (tingkat & jurusan)...'];
    
    $queries = [
        "ALTER TABLE siswa ADD COLUMN IF NOT EXISTS tingkat VARCHAR(10)" => "Menambahkan kolom 'tingkat' pada tabel siswa",
        "ALTER TABLE siswa ADD COLUMN IF NOT EXISTS jurusan VARCHAR(50)" => "Menambahkan kolom 'jurusan' pada tabel siswa",
        "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS tingkat VARCHAR(10)" => "Menambahkan kolom 'tingkat' pada tabel absensi",
        "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS jurusan VARCHAR(50)" => "Menambahkan kolom 'jurusan' pada tabel absensi",
    ];

    foreach ($queries as $sql => $desc) {
        $steps[] = ['status' => 'info', 'message' => "Executing: $desc..."];
        try {
            $koneksi->exec($sql);
            $steps[] = ['status' => 'success', 'message' => "✔️ Berhasil: $desc (atau sudah ada)."];
        } catch (PDOException $e) {
            $steps[] = ['status' => 'warning', 'message' => "⚠️ Gagal/Ada kendala: " . $e->getMessage()];
        }
    }
} else {
    $steps[] = ['status' => 'info', 'message' => 'Memulai Migrasi Skema Default Supabase...'];
    
    try {
        $koneksi->beginTransaction();

        // 1. Upgrade tabel 'siswa' - Menambahkan kolom 'password' jika belum ada
        $steps[] = ['status' => 'info', 'message' => 'Meng-upgrade tabel siswa (kolom password)...'];
        $sqlSiswa = "ALTER TABLE siswa ADD COLUMN IF NOT EXISTS password VARCHAR(255)";
        $koneksi->exec($sqlSiswa);
        $steps[] = ['status' => 'success', 'message' => '✔️ Kolom password siap pada tabel siswa.'];

        // 2. Membuat tabel 'admin' jika belum ada
        $steps[] = ['status' => 'info', 'message' => 'Membuat tabel admin jika belum ada...'];
        $sqlAdminTable = "CREATE TABLE IF NOT EXISTS admin (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nama VARCHAR(100) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'admin' CHECK (role IN ('admin', 'guru'))
        )";
        $koneksi->exec($sqlAdminTable);
        $steps[] = ['status' => 'success', 'message' => '✔️ Tabel admin siap digunakan.'];

        // 3. Masukkan data Admin Default jika belum ada
        $checkAdmin = $koneksi->prepare("SELECT COUNT(*) FROM admin WHERE username = :username");
        
        // a. Akun Admin (admin)
        $checkAdmin->execute([':username' => 'admin']);
        if ($checkAdmin->fetchColumn() == 0) {
            $steps[] = ['status' => 'info', 'message' => 'Membuat akun default admin...'];
            $hashedAdminPass = password_hash('admin123', PASSWORD_DEFAULT);
            $insertAdmin = $koneksi->prepare("INSERT INTO admin (username, password, nama, role) VALUES (:username, :password, :nama, :role)");
            $insertAdmin->execute([
                ':username' => 'admin',
                ':password' => $hashedAdminPass,
                ':nama'     => 'Aldi Admin',
                ':role'     => 'admin'
            ]);
            $steps[] = ['status' => 'success', 'message' => '✔️ Akun Admin default dibuat! (Username: admin, Password: admin123)'];
        } else {
            $steps[] = ['status' => 'warning', 'message' => '⚠️ Akun admin default sudah ada.'];
        }

        // b. Akun Guru Default
        $checkAdmin->execute([':username' => 'guru']);
        if ($checkAdmin->fetchColumn() == 0) {
            $steps[] = ['status' => 'info', 'message' => 'Membuat akun default guru...'];
            $hashedGuruPass = password_hash('guru123', PASSWORD_DEFAULT);
            $insertGuru = $koneksi->prepare("INSERT INTO admin (username, password, nama, role) VALUES (:username, :password, :nama, :role)");
            $insertGuru->execute([
                ':username' => 'guru',
                ':password' => $hashedGuruPass,
                ':nama'     => 'Guru Absensi',
                ':role'     => 'guru'
            ]);
            $steps[] = ['status' => 'success', 'message' => '✔️ Akun Guru default dibuat! (Username: guru, Password: guru123)'];
        } else {
            $steps[] = ['status' => 'warning', 'message' => '⚠️ Akun guru default sudah ada.'];
        }

        $koneksi->commit();
    } catch (Exception $e) {
        if ($koneksi->inTransaction()) {
            $koneksi->rollBack();
        }
        $success = false;
        $steps[] = ['status' => 'error', 'message' => '❌ Migrasi Gagal: ' . $e->getMessage()];
    }
}

if ($success) {
    $steps[] = ['status' => 'success', 'message' => '🎉 KONSOL: PROSES MIGRASI BERHASIL SEPENUHNYA!'];
}

echo json_encode([
    'success' => $success,
    'steps' => $steps
]);
exit;
