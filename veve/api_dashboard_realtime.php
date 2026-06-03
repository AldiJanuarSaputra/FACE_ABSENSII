<?php
session_start();

// Validasi sesi admin
if (!isset($_SESSION['admin_user'])) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Akses tidak sah."]);
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
header("Content-Type: application/json");

try {
    // 1. Hitung total siswa terdaftar
    $qSiswa = $koneksi->query("SELECT COUNT(*) FROM siswa");
    $totalSiswa = (int)$qSiswa->fetchColumn();

    // 2. Hitung statistik absensi hari ini (tanggal sekarang)
    $hariIni = date('Y-m-d');
    
    // Total Hadir hari ini
    $qHadir = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = :tgl AND status = 'Hadir'");
    $qHadir->execute([':tgl' => $hariIni]);
    $totalHadir = (int)$qHadir->fetchColumn();

    // Total Terlambat hari ini
    $qLambat = $koneksi->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = :tgl AND status = 'Terlambat'");
    $qLambat->execute([':tgl' => $hariIni]);
    $totalLambat = (int)$qLambat->fetchColumn();

    // Total Alfa hari ini
    $totalAlfa = max(0, $totalSiswa - ($totalHadir + $totalLambat));

    // 3. Query 5 log absensi terbaru secara global untuk aktivitas terupdate
    $qLogs = $koneksi->query("SELECT nis, nama, kelas, tanggal, jam, status FROM absensi ORDER BY tanggal DESC, jam DESC LIMIT 5");
    $logs = $qLogs->fetchAll(PDO::FETCH_ASSOC);

    // Format tanggal dan jam untuk kebutuhan UI yang lebih cantik
    foreach ($logs as &$log) {
        $log['tanggal_format'] = date("d/m/Y", strtotime($log['tanggal']));
        $log['jam_format'] = date("H:i", strtotime($log['jam']));
    }

    echo json_encode([
        "status" => "success",
        "stats" => [
            "totalSiswa" => $totalSiswa,
            "totalHadir" => $totalHadir,
            "totalLambat" => $totalLambat,
            "totalAlfa" => $totalAlfa
        ],
        "logs" => $logs
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>
