<?php require_once __DIR__ . '/../app/functions.php'; auth_guard(); ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?> | <?= e(APP_TAGLINE) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= build_url('/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark shadow-sm px-3">
  <a class="navbar-brand fw-bold text-white text-decoration-none" href="<?= build_url('/admin/dashboard.php') ?>">
    <?= e(APP_NAME) ?>
  </a>
  <div class="d-flex align-items-center gap-3 text-white small">
    <div>
      <div class="fw-semibold"><?= e(current_user()['nama_lengkap'] ?? 'Pengguna') ?></div>
      <div class="opacity-75 text-end"><?= e(current_user()['role'] ?? '-') ?></div>
    </div>
    <a class="btn btn-sm btn-outline-light" href="<?= build_url('/auth/logout.php') ?>">Logout</a>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <aside class="col-lg-2 sidebar p-3">
      <div class="sidebar-card mb-3">
        <div class="fw-bold text-white">Menu</div>
        <div class="small text-white-50">Monitoring proyek infrastruktur</div>
      </div>
      <a href="<?= build_url('/admin/dashboard.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/admin/dashboard.php') ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= build_url('/projects/index.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/projects/') ? 'active' : '' ?>">Data Proyek</a>
      <a href="<?= build_url('/progress/index.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/progress/') ? 'active' : '' ?>">Progres Lapangan</a>
      <a href="<?= build_url('/compliance/index.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/compliance/') ? 'active' : '' ?>">Kepatuhan</a>
      <a href="<?= build_url('/reports/index.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/reports/') ? 'active' : '' ?>">Laporan</a>
      <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
        <a href="<?= build_url('/users/index.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/users/') ? 'active' : '' ?>">Pengguna & RBAC</a>
      <?php endif; ?>
      <a href="<?= build_url('/logs/index.php') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], '/logs/') ? 'active' : '' ?>">Audit Log</a>
    </aside>
    <main class="col-lg-10 p-4">
