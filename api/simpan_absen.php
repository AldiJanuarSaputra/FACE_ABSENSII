<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Set timezone ke Waktu Indonesia Barat (WIB) agar jam sinkron
date_default_timezone_set("Asia/Jakarta");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
if(!$data){
    echo json_encode(["sukses"=>false,"pesan"=>"Data tidak valid"]);
    exit;
}

$nis   = $data['nis']   ?? '';
$nama  = $data['nama']  ?? '';
$kelas = $data['kelas'] ?? '';

if(!$nis || !$nama){
    echo json_encode(["sukses"=>false,"pesan"=>"Data siswa tidak lengkap"]);
    exit;
}

// Ambil data tingkat & jurusan dari tabel siswa
$tingkat = '';
$jurusan = '';
try {
    $stmtSiswa = $koneksi->prepare("SELECT tingkat, jurusan FROM siswa WHERE nis = :nis LIMIT 1");
    $stmtSiswa->execute([':nis' => $nis]);
    $dataSiswa = $stmtSiswa->fetch(PDO::FETCH_ASSOC);
    if ($dataSiswa) {
        $tingkat = $dataSiswa['tingkat'] ?? '';
        $jurusan = $dataSiswa['jurusan'] ?? '';
    }
} catch (PDOException $e) {
    // Lanjutkan tanpa tingkat/jurusan jika gagal
}

$tanggal = date("Y-m-d");
$jam     = date("H:i:s");

try {
    // Cek apakah sudah absen hari ini
    $cekStmt = $koneksi->prepare("SELECT id, jam, status FROM absensi WHERE nis = :nis AND tanggal = :tanggal LIMIT 1");
    $cekStmt->execute([
        ':nis' => $nis,
        ':tanggal' => $tanggal
    ]);
    
    $row = $cekStmt->fetch(PDO::FETCH_ASSOC);

    if($row){
        echo json_encode([
            "sukses"  => false,
            "pesan"   => "Sudah absen hari ini pukul ".$row['jam'],
            "nis"     => $nis,
            "nama"    => $nama,
            "kelas"   => $kelas,
            "tingkat" => $tingkat,
            "jurusan" => $jurusan,
            "waktu"   => $row['jam'],
            "status"  => $row['status']
        ]);
        exit;
    }

    // Tentukan status berdasar jam (Batas maksimal jam 07:00 pagi)
    $jamInt = (int)date("Hi"); // contoh: 0700, 0830, dsb
    if($jamInt <= 700){
        $status = "Hadir";
    }else{
        $status = "Terlambat";
    }

    $sql = "INSERT INTO absensi(nis,nama,kelas,tingkat,jurusan,tanggal,jam,status)
            VALUES(:nis, :nama, :kelas, :tingkat, :jurusan, :tanggal, :jam, :status)";
    $stmt = $koneksi->prepare($sql);
    $sukses = $stmt->execute([
        ':nis' => $nis,
        ':nama' => $nama,
        ':kelas' => $kelas,
        ':tingkat' => $tingkat,
        ':jurusan' => $jurusan,
        ':tanggal' => $tanggal,
        ':jam' => $jam,
        ':status' => $status
    ]);

    if($sukses){
        echo json_encode([
            "sukses"  => true,
            "pesan"   => "Absensi berhasil disimpan",
            "nis"     => $nis,
            "nama"    => $nama,
            "kelas"   => $kelas,
            "tingkat" => $tingkat,
            "jurusan" => $jurusan,
            "waktu"   => $jam,
            "status"  => $status
        ]);
    }else{
        echo json_encode([
            "sukses" => false,
            "pesan"  => "Gagal menyimpan data absensi"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "sukses" => false,
        "pesan"  => "Error DB: " . $e->getMessage()
    ]);
}
?>
