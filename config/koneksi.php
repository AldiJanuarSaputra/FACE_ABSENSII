<?php

// 1. Fungsi sederhana untuk me-load file .env secara manual (tanpa Composer)
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
loadEnv(__DIR__ . '/../.env');

// 2. Mengambil kredensial database dari environment variables (dengan Fallback Keamanan)
$host = getenv('DB_HOST') ?: "aws-1-ap-south-1.pooler.supabase.com"; 
$port = getenv('DB_PORT') ?: "6543";                                     
$user = getenv('DB_USER') ?: "postgres.cgnztnnflygpdpfdsrmm";             
$pass = getenv('DB_PASS') ?: "faceabsensi123";                            
$db   = getenv('DB_NAME') ?: "postgres";                                  

// 3. Melakukan Koneksi ke PostgreSQL Supabase via PDO
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";
    
    $koneksi = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan Mode Error Exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis berbentuk Array Asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Menonaktifkan emulasi agar lebih aman dari SQL Injection
    ]);

} catch (PDOException $e) {
    // Jika gagal, hentikan aplikasi dan munculkan pesan error
    die("Koneksi ke Database Supabase Gagal: " . $e->getMessage());
}