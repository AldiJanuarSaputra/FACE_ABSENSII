<?php
include "koneksi.php";
$pesan = ''; $tipePesan = 'info';

if (isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nis   = trim($_POST['nis']   ?? '');
    $nama  = trim($_POST['nama']  ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    if (!$nis || !$nama || !$kelas) {
        $pesan = "Semua kolom harus diisi!"; $tipePesan = "err";
    } else {
        try {
            $cek = $koneksi->prepare("SELECT id FROM siswa WHERE nis = :nis");
            $cek->execute([':nis' => $nis]);
            if ($cek->fetch()) {
                $pesan = "NIS $nis sudah terdaftar!"; $tipePesan = "err";
            } else {
                $stmt = $koneksi->prepare("INSERT INTO siswa (nis, nama, kelas) VALUES (:nis, :nama, :kelas)");
                $stmt->execute([':nis'=>$nis, ':nama'=>$nama, ':kelas'=>$kelas]);
                header("Location: siswa.php?pesan=tambah_ok");
                exit;
            }
        } catch (PDOException $e) {
            $pesan = "Error: " . $e->getMessage(); $tipePesan = "err";
        }
    }
}

if ($pesan) {
    header("Location: siswa.php?pesan_err=" . urlencode($pesan));
    exit;
}
header("Location: siswa.php");
exit;
?>
