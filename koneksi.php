<?php

$host = "aws-1-ap-south-1.pooler.supabase.com"; // Host IPv4 Pooler Supabase (Mumbai AWS-1)
$port = "6543";                                     // Port Connection Pooler (Paling Stabil)
$user = "postgres.cgnztnnflygpdpfdsrmm";             // Username pooler format: postgres.[project-ref]
$pass = "faceabsensi123";                            // Password database Supabase Anda
$db   = "postgres";                                  // Default database name

try {
    $koneksi = new PDO("pgsql:host=$host;port=$port;dbname=$db;sslmode=require", $user, $pass);
    // Set error mode ke exception agar mempermudah debugging jika ada error
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

?>