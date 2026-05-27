<?php
include "koneksi.php";

echo "<h2>Migrasi Database: Menambahkan kolom tingkat & jurusan</h2>";

$queries = [
    "ALTER TABLE siswa ADD COLUMN IF NOT EXISTS tingkat VARCHAR(10)",
    "ALTER TABLE siswa ADD COLUMN IF NOT EXISTS jurusan VARCHAR(50)",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS tingkat VARCHAR(10)",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS jurusan VARCHAR(50)",
];

foreach ($queries as $sql) {
    try {
        $koneksi->exec($sql);
        echo "<p style='color:green'>✅ OK: $sql</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange'>⚠️ $sql — " . $e->getMessage() . "</p>";
    }
}

echo "<br><a href='../index.php'>← Kembali ke Dashboard</a>";
?>
