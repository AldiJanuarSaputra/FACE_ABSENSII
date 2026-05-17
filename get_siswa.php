<?php
include "koneksi.php";

header("Content-Type: application/json");

try {
    // Ambil semua siswa beserta face descriptor
    $sql  = "SELECT nis, nama, kelas, descriptor FROM siswa WHERE descriptor IS NOT NULL";
    $stmt = $koneksi->query($sql);

    $list = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $desc = json_decode($row['descriptor'], true);
        if(!$desc || count($desc) !== 128) continue;  // skip jika descriptor invalid

        $list[] = [
            "nis"        => $row['nis'],
            "nama"       => $row['nama'],
            "kelas"      => $row['kelas'],
            "descriptor" => $desc
        ];
    }

    echo json_encode($list);
} catch (PDOException $e) {
    echo json_encode(["sukses" => false, "pesan" => "Error DB: " . $e->getMessage()]);
}
?>
