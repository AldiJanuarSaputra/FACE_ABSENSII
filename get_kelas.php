<?php
include "koneksi.php";
header("Content-Type: application/json");
try {
    $stmt = $koneksi->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas <> '' ORDER BY kelas ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($rows);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
