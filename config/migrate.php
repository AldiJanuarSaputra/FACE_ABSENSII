<?php

// Tampilkan semua error untuk mempermudah debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚀 Memulai Migrasi Database Supabase...</h2>";

// 1. Hubungkan ke database
require_once __DIR__ . '/koneksi.php';

if (!isset($koneksi)) {
    die("<h3 style='color:red;'>❌ Gagal terhubung ke database. Cek file koneksi.php dan .env Anda.</h3>");
}

echo "<p style='color:green;'>✔️ Berhasil terhubung ke database Supabase PostgreSQL!</p>";

try {
    // Begin transaction
    $koneksi->beginTransaction();

    // 2. Upgrade tabel 'siswa' - Menambahkan kolom 'password' jika belum ada
    echo "<p>Meng-upgrade tabel <b>siswa</b>...</p>";
    $sqlSiswa = "ALTER TABLE siswa ADD COLUMN IF NOT EXISTS password VARCHAR(255)";
    $koneksi->exec($sqlSiswa);
    echo "<p style='color:green;'>✔️ Kolom 'password' berhasil ditambahkan ke tabel 'siswa' (atau sudah ada)!</p>";

    // 3. Membuat tabel 'admin' jika belum ada
    echo "<p>Membuat tabel <b>admin</b>...</p>";
    $sqlAdminTable = "CREATE TABLE IF NOT EXISTS admin (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'admin' CHECK (role IN ('admin', 'guru'))
    )";
    $koneksi->exec($sqlAdminTable);
    echo "<p style='color:green;'>✔️ Tabel 'admin' berhasil dibuat (atau sudah ada)!</p>";

    // 4. Masukkan data Admin Default jika belum ada
    $checkAdmin = $koneksi->prepare("SELECT COUNT(*) FROM admin WHERE username = :username");
    
    // a. Akun Admin (Aldi)
    $checkAdmin->execute([':username' => 'admin']);
    if ($checkAdmin->fetchColumn() == 0) {
        echo "<p>Membuat akun default <b>admin</b>...</p>";
        $hashedAdminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = $koneksi->prepare("INSERT INTO admin (username, password, nama, role) VALUES (:username, :password, :nama, :role)");
        $insertAdmin->execute([
            ':username' => 'admin',
            ':password' => $hashedAdminPass,
            ':nama'     => 'Aldi Admin',
            ':role'     => 'admin'
        ]);
        echo "<p style='color:green;'>✔️ Akun Admin default dibuat! (Username: <b>admin</b>, Password: <b>admin123</b>)</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Akun admin default sudah ada.</p>";
    }

    // b. Akun Guru Default
    $checkAdmin->execute([':username' => 'guru']);
    if ($checkAdmin->fetchColumn() == 0) {
        echo "<p>Membuat akun default <b>guru</b>...</p>";
        $hashedGuruPass = password_hash('guru123', PASSWORD_DEFAULT);
        $insertGuru = $koneksi->prepare("INSERT INTO admin (username, password, nama, role) VALUES (:username, :password, :nama, :role)");
        $insertGuru->execute([
            ':username' => 'guru',
            ':password' => $hashedGuruPass,
            ':nama'     => 'Guru Absensi',
            ':role'     => 'guru'
        ]);
        echo "<p style='color:green;'>✔️ Akun Guru default dibuat! (Username: <b>guru</b>, Password: <b>guru123</b>)</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Akun guru default sudah ada.</p>";
    }

    // Commit Transaction
    $koneksi->commit();
    echo "<h3 style='color:green;'>🎉 MIGRASI DATABASE BERHASIL SEPENUHNYA!</h3>";

} catch (Exception $e) {
    // Rollback jika ada error
    if ($koneksi->inTransaction()) {
        $koneksi->rollBack();
    }
    echo "<h3 style='color:red;'>❌ Migrasi Gagal: " . $e->getMessage() . "</h3>";
}

?>
