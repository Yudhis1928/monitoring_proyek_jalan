<?php
// 1. Inisialisasi sesi wajib diletakkan di baris paling atas proyek serverless
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Aktifkan pelacak error untuk debugging internal
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Muat file fungsi inti agar metrik dan query database dapat dieksekusi
require_once __DIR__ . '/../app/functions.php';

// 4. Amankan halaman: Pengguna harus lolos pengecekan login sebelum header dimuat
auth_guard();

// 5. Muat komponen desain pembungkus atas (Header)
require_once __DIR__ . '/../partials/header.php'; 

// ==========================================
// PROSES DATA METRIK & DEVIASI PROYEK
// ==========================================
$metrics = dashboard_metrics();
$recentProjects = recent_projects(4);
$recentProgress = recent_progress(5);
$recentCompliance = recent_compliance(5);
$projects = get_projects();
$attention = [];

foreach ($projects as $p) {
    $latest = get_latest_progress((int)$p['id_proyek']);
    $progress = (float)($latest['progress_persen'] ?? $p['persentase_progress'] ?? 0);
    $expected = expected_progress($p);
    if ($expected - $progress > 15 && $p['status'] !== 'Selesai') {
        $attention[] = [
            'nama_proyek' => $p['nama_proyek'],
            'kode_proyek' => $p['kode_proyek'],
            'progress' => $progress,
            'expected' => $expected
        ];
    }
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h2 class="mb-1">Dashboard Monitoring</h2>
    <div class="small-muted">Ringkasan progres, kepatuhan, dan status proyek infrastruktur.</div>
  </div>
  <div class="print-hidden">
    <a href="<?= build_url('/reports/index.php') ?>" class="btn btn-outline-primary">Buka Laporan</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card-soft p-3"><div class="small-muted">Total Proyek</div><div class="metric"><?= $metrics['total_projects'] ?></div></div></div>
  <div class="col-md-3"><div class="card-soft p-3"><div class="small-muted">Sedang Berjalan</div><div class="metric"><?= $metrics['running_projects'] ?></div></div></div>
  <div class="col-md-3"><div class="card-soft p-3"><div class="small-muted">Selesai</div><div class="metric"><?= $metrics['completed_projects'] ?></div></div></div>
  <div class="col-md-3"><div class="card-soft p-3"><div class="small-muted">Rata-rata Progres</div><div class="metric"><?= $metrics['avg_progress'] ?>%</div></div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card-soft p-3"><div class="small-muted">Proyek Terlambat</div><h3 class="mb-0"><?= $metrics['late_projects'] ?></h3></div></div>
  <div class="col-md-4"><div class="card-soft p-3"><div class="small-muted">Perlu Perhatian</div><h3 class="mb-0"><?= $metrics['attention_projects'] ?></h3></div></div>
  <div class="col-md-4"><div class="card-soft p-3"><div class="small-muted">Skor Kepatuhan</div><h3 class="mb-0"><?= $metrics['compliance_rate'] ?>%</h3></div></div>
</div>

<?php if ($attention): ?>
<div class="card-soft p-4 mb-4">
  <h5 class="mb-3">Notifikasi Deviasi</h5>
  <div class="alert alert-warning mb-0">
    <?= count($attention) ?> proyek tertinggal dari target progres. Tentu saja, jadwal proyek tetap punya bakat dramatis.
  </div>
  <div class="table-responsive mt-3">
    <table class="table table-sm align-middle">
      <thead>
        <tr><th>Kode</th><th>Proyek</th><th>Progres Aktual</th><th>Target Sistem</th><th>Selisih</th></tr>
      </thead>
      <tbody>
      <?php foreach (array_slice($attention, 0, 5) as $item): ?>
        <tr>
          <td><?= e($item['kode_proyek']) ?></td>
          <td><?= e($item['nama_proyek']) ?></td>
          <td><?= number_format($item['progress'], 1) ?>%</td>
          <td><?= number_format($item['expected'], 1) ?>%</td>
          <td class="text-danger"><?= number_format($item['expected'] - $item['progress'], 1) ?>%</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-soft p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Proyek Terbaru</h5>
        <a href="<?= build_url('/projects/index.php') ?>" class="small text-decoration-none">Kelola</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>Kode</th><th>Nama</th><th>Status</th><th>Progres</th></tr></thead>
          <tbody>
          <?php foreach ($recentProjects as $p): ?>
            <tr>
              <td><?= e($p['kode_proyek']) ?></td>
              <td><?= e($p['nama_proyek']) ?></td>
              <td><?= status_badge($p['status']) ?></td>
              <td><?= number_format($p['persentase_progress'], 1) ?>%</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-soft p-4 h-100">
      <h5 class="mb-3">Aktivitas Lapangan Terbaru</h5>
      <div class="list-group list-group-flush">
        <?php foreach ($recentProgress as $row): ?>
          <div class="list-group-item px-0">
            <div class="d-flex justify-content-between">
              <strong><?= e($row['nama_proyek']) ?></strong>
              <span><?= number_format($row['progress_persen'], 1) ?>%</span>
            </div>
            <div class="small text-muted"><?= e($row['uraian']) ?> · <?= format_date($row['tanggal_laporan']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="card-soft p-4 mt-4">
  <h5 class="mb-3">Status Kepatuhan Terkini</h5>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Kode</th><th>Proyek</th><th>Aspek</th><th>Hasil</th><th>Tanggal</th></tr></thead>
      <tbody>
      <?php foreach ($recentCompliance as $row): ?>
        <tr>
          <td><?= e($row['kode_proyek']) ?></td>
          <td><?= e($row['nama_proyek']) ?></td>
          <td><?= e($row['aspek']) ?></td>
          <td><?= status_badge($row['status_hasil']) ?></td>
          <td><?= format_date($row['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
