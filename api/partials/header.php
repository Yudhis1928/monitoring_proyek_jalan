<?php
// 1. Pastikan sesi sudah berjalan dengan aman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Amankan jalur file fungsi jika belum termuat
require_once __DIR__ . '/../app/functions.php';

// 3. Ambil data pengguna yang sedang aktif
$user = current_user();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?><?= defined('APP_NAME') ? e(APP_NAME) : 'Monitoring Jalan' ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= build_url('/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 print-hidden">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= build_url('/admin/dashboard.php') ?>">
      <span>📊</span> <?= defined('APP_NAME') ? e(APP_NAME) : 'Monitoring Jalan' ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= build_url('/admin/dashboard.php') ?>">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= build_url('/projects/index.php') ?>">Data Proyek</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= build_url('/reports/index.php') ?>">Laporan</a>
        </li>
      </ul>
      <div class="navbar-nav ms-auto align-items-center">
        <span class="navbar-text text-white me-3 d-none d-lg-inline">
          Halo, <strong><?= e($user['nama_lengkap'] ?? 'Pengguna') ?></strong> <span class="badge text-bg-info ms-1"><?= e($user['role'] ?? 'User') ?></span>
        </span>
        <a class="btn btn-outline-danger btn-sm" href="<?= build_url('/auth/logout.php') ?>">Keluar</a>
      </div>
    </div>
  </div>
</nav>

<div class="container mb-5">
