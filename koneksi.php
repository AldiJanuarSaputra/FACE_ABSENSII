<?php

// Fungsi sederhana untuk me-load file .env secara manual (tanpa Composer)
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Abaikan baris komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Pisahkan nama dan nilai variabel
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Bersihkan tanda kutip jika ada
            $value = trim($value, "\"'");
            
            // Masukkan ke environment variable jika belum di-set
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load file .env dari folder utama proyek
loadEnv(__DIR__ . '/.env');

// Mengambil kredensial database dari environment variables
$host = getenv('DB_HOST') ?: "aws-1-ap-south-1.pooler.supabase.com"; 
$port = getenv('DB_PORT') ?: "6543";                                     
$user = getenv('DB_USER') ?: "postgres.cgnztnnflygpdpfdsrmm";             
$pass = getenv('DB_PASS') ?: "faceabsensi123";                            
$db   = getenv('DB_NAME') ?: "postgres";                                  

try {
    $koneksi = new PDO("pgsql:host=$host;port=$port;dbname=$db;sslmode=require", $user, $pass);
    // Set error mode ke exception agar mempermudah debugging jika ada error
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

?>