<?php
require_once __DIR__ . '/koneksi.php';

try {
    if (isset($koneksi)) {
        echo "Koneksi Berhasil!";
    } else {
        echo "Gagal: Variabel koneksi tidak terdefinisi.";
    }
} catch (Exception $e) {
    echo "Gagal: " . $e->getMessage();
}

