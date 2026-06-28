<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php
$projects = get_projects();
$metrics = dashboard_metrics();
$progressRows = recent_progress(100);
$compRows = recent_compliance(100);
?>
<div class="d-flex justify-content-between align-items-end gap-3 mb-4 print-hidden">
  <div>
    <h2 class="mb-1">Laporan Monitoring</h2>
    <div class="small-muted">Rekap singkat yang bisa dicetak untuk rapat, presentasi, atau sekadar formalitas manusia.</div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary" onclick="window.print()">Cetak</button>
    <a class="btn btn-primary" href="<?= build_url('/admin/dashboard.php') ?>">Kembali</a>
  </div>
</div>

<div class="card-soft p-4 mb-4">
  <h5 class="mb-3">Ringkasan Umum</h5>
  <div class="row g-3">
    <div class="col-md-3"><div class="p-3 bg-light rounded-4"><div class="small-muted">Total Proyek</div><div class="metric"><?= $metrics['total_projects'] ?></div></div></div>
    <div class="col-md-3"><div class="p-3 bg-light rounded-4"><div class="small-muted">Rata-rata Progres</div><div class="metric"><?= $metrics['avg_progress'] ?>%</div></div></div>
    <div class="col-md-3"><div class="p-3 bg-light rounded-4"><div class="small-muted">Perlu Perhatian</div><div class="metric"><?= $metrics['attention_projects'] ?></div></div></div>
    <div class="col-md-3"><div class="p-3 bg-light rounded-4"><div class="small-muted">Skor Kepatuhan</div><div class="metric"><?= $metrics['compliance_rate'] ?>%</div></div></div>
  </div>
</div>

<div class="card-soft p-4 mb-4">
  <h5 class="mb-3">Daftar Proyek</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Lokasi</th><th>Anggaran</th><th>Progres</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td><?= e($p['kode_proyek']) ?></td>
          <td><?= e($p['nama_proyek']) ?></td>
          <td><?= e($p['jenis_proyek']) ?></td>
          <td><?= e($p['lokasi']) ?></td>
          <td><?= rupiah($p['nilai_anggaran']) ?></td>
          <td><?= number_format($p['persentase_progress'], 1) ?>%</td>
          <td><?= status_badge($p['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card-soft p-4 mb-4">
  <h5 class="mb-3">Rekap Progres Lapangan</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Tanggal</th><th>Proyek</th><th>Progres</th><th>Petugas</th><th>Uraian</th></tr></thead>
      <tbody>
      <?php foreach ($progressRows as $row): ?>
        <tr>
          <td><?= format_date($row['tanggal_laporan']) ?></td>
          <td><?= e($row['nama_proyek']) ?></td>
          <td><?= number_format($row['progress_persen'], 1) ?>%</td>
          <td><?= e($row['petugas']) ?></td>
          <td><?= e($row['uraian']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card-soft p-4">
  <h5 class="mb-3">Rekap Kepatuhan</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Tanggal</th><th>Proyek</th><th>Aspek</th><th>Hasil</th><th>Catatan</th></tr></thead>
      <tbody>
      <?php foreach ($compRows as $row): ?>
        <tr>
          <td><?= format_date($row['created_at']) ?></td>
          <td><?= e($row['nama_proyek']) ?></td>
          <td><?= e($row['aspek']) ?></td>
          <td><?= status_badge($row['status_hasil']) ?></td>
          <td><?= e($row['catatan']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
