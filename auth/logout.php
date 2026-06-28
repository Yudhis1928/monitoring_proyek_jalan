<?php
require_once __DIR__ . '/../app/functions.php';
record_log('LOGOUT', 'Pengguna keluar dari sistem');
session_destroy();
header('Location: ' . build_url('/auth/login.php'));
exit;
