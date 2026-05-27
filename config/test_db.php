<?php
$host = "aws-1-ap-south-1.pooler.supabase.com"; 
$port = "6543";                                     
$user = "postgres.cgnztnnflygpdpfdsrmm";             
$pass = "faceabsensi123";                            
$db   = "postgres";                                  

try {
    $koneksi = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi Berhasil!";
} catch (PDOException $e) {
    echo "Gagal: " . $e->getMessage();
}
