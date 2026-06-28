<?php
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'monitoring_proyek_scrum');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('APP_NAME', 'Manajemen Infrastruktur Publik');
    define('APP_TAGLINE', 'Jalan, Jembatan, dan Fasilitas Umum');
    define('BASE_URL', '/monitoring_proyek_jalan'); 
    define('UPLOAD_DIR', __DIR__ . '/../uploads');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
