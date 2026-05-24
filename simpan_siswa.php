<?php
session_start();
include "koneksi.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo "Data tidak valid";
    exit;
}

$nis        = $data['nis']        ?? '';
$nama       = $data['nama']       ?? '';
$kelas      = $data['kelas']      ?? '';
$kelas_id   = !empty($data['kelas_id']) ? (int)$data['kelas_id'] : null;
$tingkat    = $data['tingkat']    ?? '';
$jurusan    = $data['jurusan']    ?? '';
$password   = $data['password']   ?? '';
$wajah      = $data['wajah']       ?? '';  // base64 image
$descriptor = json_encode($data['descriptor'] ?? []); // array 128 float

if(!$nis || !$nama || !$kelas){
    echo "Data tidak lengkap";
    exit;
}

try {
    // Cek apakah NIS sudah terdaftar
    $cekStmt = $koneksi->prepare("SELECT id FROM siswa WHERE nis = :nis");
    $cekStmt->execute([':nis' => $nis]);
    $row = $cekStmt->fetch(PDO::FETCH_ASSOC);

    if($row){
        // Update jika sudah ada
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE siswa SET nama = :nama, kelas = :kelas, kelas_id = :kelas_id,
                    tingkat = :tingkat, jurusan = :jurusan,
                    wajah = :wajah, descriptor = :descriptor, password = :password
                    WHERE nis = :nis";
            $stmt = $koneksi->prepare($sql);
            $sukses = $stmt->execute([
                ':nama' => $nama,
                ':kelas' => $kelas,
                ':kelas_id' => $kelas_id,
                ':tingkat' => $tingkat,
                ':jurusan' => $jurusan,
                ':wajah' => $wajah,
                ':descriptor' => $descriptor,
                ':password' => $hashed,
                ':nis' => $nis
            ]);
        } else {
            $sql = "UPDATE siswa SET nama = :nama, kelas = :kelas, kelas_id = :kelas_id,
                    tingkat = :tingkat, jurusan = :jurusan,
                    wajah = :wajah, descriptor = :descriptor
                    WHERE nis = :nis";
            $stmt = $koneksi->prepare($sql);
            $sukses = $stmt->execute([
                ':nama' => $nama,
                ':kelas' => $kelas,
                ':kelas_id' => $kelas_id,
                ':tingkat' => $tingkat,
                ':jurusan' => $jurusan,
                ':wajah' => $wajah,
                ':descriptor' => $descriptor,
                ':nis' => $nis
            ]);
        }
        if($sukses){
            echo "Data berhasil diperbarui (NIS: $nis)";
        }else{
            echo "Gagal memperbarui data";
        }
    }else{
        // Insert baru
        $hashed = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : null;
        $sql = "INSERT INTO siswa(nis,nama,kelas,kelas_id,tingkat,jurusan,wajah,descriptor,password)
                VALUES(:nis, :nama, :kelas, :kelas_id, :tingkat, :jurusan, :wajah, :descriptor, :password)";
        $stmt = $koneksi->prepare($sql);
        $sukses = $stmt->execute([
            ':nis' => $nis,
            ':nama' => $nama,
            ':kelas' => $kelas,
            ':kelas_id' => $kelas_id,
            ':tingkat' => $tingkat,
            ':jurusan' => $jurusan,
            ':wajah' => $wajah,
            ':descriptor' => $descriptor,
            ':password' => $hashed
        ]);
        if($sukses){
            echo "Registrasi berhasil (NIS: $nis)";
        }else{
            echo "Gagal menambahkan data baru";
        }
    }
} catch (PDOException $e) {
    echo "Error DB: " . $e->getMessage();
}
?>