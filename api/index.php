<?php
// Pastikan sesi aktif di file gerbang utama ini
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hubungkan inti fungsi aplikasi
require_once __DIR__ . '/app/functions.php';

// Validasi status login pengguna untuk memecah loop pengalihan
if (is_login()) {
    redirect('/admin/dashboard.php');
} else {
    redirect('/auth/login.php');
}
exit;
?>
