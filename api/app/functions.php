<?php
// === TAMBAHKAN KODE INI DI BARIS PALING ATAS ===
if (!defined('BASE_URL')) {
    // Mendeteksi protokol (http atau https) secara otomatis
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    // Menggabungkan protokol dengan domain yang sedang aktif di Vercel / Localhost
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . $domain);
}
// ===============================================

// ... sisa kode fungsi Anda yang asli di bawah (seperti fungsi build_url atau redirect) ...
