<?php
// 1. Pastikan inisialisasi sesi berjalan paling awal di serverless environment
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Aktifkan pelacak error untuk debugging jika terjadi sesuatu
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Panggil fungsi core dengan jalur absolut yang aman
require_once __DIR__ . '/../app/functions.php';

// 4. Proteksi Halaman: Pastikan pengguna sudah login dan memiliki role yang sesuai
// Jika fungsi ini memicu loop, pastikan ejaan role di database Anda (misal: 'Admin') cocok
auth_guard(); 

// Ambil data metrik untuk ditampilkan di dashboard
$metrics = dashboard_metrics();
$recent_p = recent_projects(5);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin | <?= e(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= build_url('/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="#"><?= e(APP_NAME) ?></a>
    <div class="navbar-nav ms-auto">
      <span class="nav-link text-white me-3">Halo, <?= e(current_user()['nama_lengkap'] ?? 'Pengguna') ?></span>
      <a class="btn btn-danger btn-sm" href="<?= build_url('/auth/logout.php') ?>">Keluar</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="row mb-4">
    <div class="col-12">
      <h2>Ringkasan Proyek Jalan</h2>
      <p class="text-muted">Selamat datang di sistem monitoring kepatuhan dan progres infrastruktur jalan.</p>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card p-3 border-0 bg-light shadow-sm">
        <h6 class="text-muted mb-1">Total Proyek</h6>
        <h3 class="mb-0"><?= $metrics['total_projects'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 border-0 bg-light shadow-sm">
        <h6 class="text-primary mb-1">Sedang Berjalan</h6>
        <h3 class="mb-0"><?= $metrics['running_projects'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 border-0 bg-light shadow-sm">
        <h6 class="text-success mb-1">Selesai</h6>
        <h3 class="mb-0"><?= $metrics['completed_projects'] ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 border-0 bg-light shadow-sm">
        <h6 class="text-danger mb-1">Rata-rata Progres</h6>
        <h3 class="mb-0"><?= $metrics['avg_progress'] ?>%</h3>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card p-4 border-0 shadow-sm">
        <h5 class="mb-3">Aktivitas Proyek Terbaru</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Kode</th>
                <th>Nama Proyek</th>
                <th>Tanggal Mulai</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_p)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">Belum ada data proyek di database Clever Cloud.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recent_p as $p): ?>
                  <tr>
                    <td><strong><?= e($p['kode_proyek'] ?? '-') ?></strong></td>
                    <td><?= e($p['nama_proyek'] ?? '-') ?></td>
                    <td><?= format_date($p['tanggal_mulai'] ?? '') ?></td>
                    <td><?= status_badge($p['status'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
