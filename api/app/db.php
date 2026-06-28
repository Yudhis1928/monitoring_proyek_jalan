<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kredensial database Clever Cloud Anda
define('DB_HOST', 'bsfqntekdjqnvdoah3qc-mysql.services.clever-cloud.com');
define('DB_USER', 'uw51gprlhmkkw8r8');
define('DB_PASS', 'LUEGjpHB8XoVOkhvi9EH');
define('DB_NAME', 'bsfqntekdjqnvdoah3qc');

// Membuat koneksi tunggal berbasis MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi database ke Clever Cloud gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// === SOLUSI UTAMA: Paksa PHP Menutup Koneksi MySQLi Saat Skrip Selesai ===
register_shutdown_function(function() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
});
?>
