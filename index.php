<?php
require_once __DIR__ . '/app/functions.php';
if (is_login()) {
    redirect('/admin/dashboard.php');
}
redirect('/auth/login.php');
