<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php
if (!in_array(current_user()['role'] ?? '', ['admin', 'project_manager', 'field_supervisor'], true)) {
    die('Akses ditolak.');
}

$projects = get_projects();
$selected = (int)($_GET['id_proyek'] ?? ($projects[0]['id_proyek'] ?? 0));
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_progress = (int)($_POST['id_progress'] ?? 0);
    $id_proyek = (int)($_POST['id_proyek'] ?? 0);
    $tanggal = $_POST['tanggal_laporan'] ?? date('Y-m-d');
    $progress = (float)($_POST['progress_persen'] ?? 0);
    $uraian = trim($_POST['uraian'] ?? '');
    $petugas = trim($_POST['petugas'] ?? '');
    $dok = save_upload('dokumentasi');

    if ($id_progress) {
        $old = fetch_one("SELECT dokumentasi FROM progress_reports WHERE id_progress = {$id_progress}");
        $stmt = $conn->prepare("UPDATE progress_reports SET id_proyek=?, tanggal_laporan=?, progress_persen=?, uraian=?, petugas=?, dokumentasi=COALESCE(?, dokumentasi), updated_at=NOW() WHERE id_progress=?");
        $stmt->bind_param("isdsssi", $id_proyek, $tanggal, $progress, $uraian, $petugas, $dok, $id_progress);
        $stmt->execute();
        if ($dok && !empty($old['dokumentasi'])) delete_upload($old['dokumentasi']);
        flash('success', 'Laporan progres berhasil diperbarui.');
        record_log('UPDATE_PROGRESS', "Memperbarui progres proyek ID {$id_proyek}");
    } else {
        $stmt = $conn->prepare("INSERT INTO progress_reports (id_proyek, tanggal_laporan, progress_persen, uraian, petugas, dokumentasi) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isdsss", $id_proyek, $tanggal, $progress, $uraian, $petugas, $dok);
        $stmt->execute();
        flash('success', 'Laporan progres berhasil disimpan.');
        record_log('TAMBAH_PROGRESS', "Menambah progres proyek ID {$id_proyek}");
    }

    $latest = get_latest_progress($id_proyek);
    if ($latest) {
        $stmt = $conn->prepare("UPDATE projects SET persentase_progress=?, status=CASE WHEN ? >= 100 THEN 'Selesai' WHEN ? < expected_progress AND status <> 'Perencanaan' THEN 'Terlambat' ELSE 'Berjalan' END, updated_at=NOW() WHERE id_proyek=?");
        // MySQL tidak punya expected_progress, jadi status akan disederhanakan di bawah.
    }

    $stmt = $conn->prepare("UPDATE projects SET persentase_progress=?, status=CASE WHEN ? >= 100 THEN 'Selesai' WHEN status='Perencanaan' THEN 'Perencanaan' ELSE 'Berjalan' END, updated_at=NOW() WHERE id_proyek=?");
    $stmt->bind_param("ddi", $progress, $progress, $id_proyek);
    $stmt->execute();

    $project = get_project_by_id($id_proyek);
    if ($project && $project['status'] !== 'Selesai') {
        $expected = expected_progress($project);
        if ($expected - $progress > 15) {
            $stmt = $conn->prepare("UPDATE projects SET status='Terlambat' WHERE id_proyek=?");
            $stmt->bind_param("i", $id_proyek);
            $stmt->execute();
        }
    }

    redirect('/progress/index.php?id_proyek=' . $id_proyek);
}

if ($action === 'delete' && $id) {
    $row = fetch_one("SELECT * FROM progress_reports WHERE id_progress = {$id}");
    if ($row) {
        delete_upload($row['dokumentasi'] ?? null);
    }
    $stmt = $conn->prepare("DELETE FROM progress_reports WHERE id_progress = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Laporan progres berhasil dihapus.');
    record_log('HAPUS_PROGRESS', "Menghapus laporan progres ID {$id}");
    redirect('/progress/index.php?id_proyek=' . $selected);
}

$edit = ($action === 'edit' && $id) ? fetch_one("SELECT * FROM progress_reports WHERE id_progress = {$id}") : null;
if ($edit) $selected = (int)$edit['id_proyek'];
$history = $selected ? get_project_progress($selected) : [];
?>
<div class="d-flex justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h2 class="mb-1">Progres Lapangan</h2>
    <div class="small-muted">Unggah pembaruan, dokumentasi, dan riwayat perkembangan pekerjaan.</div>
  </div>
</div>

<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-auto-dismiss><?= e($msg) ?></div><?php endif; ?>

<div class="card-soft p-4 mb-4">
  <form method="get" class="row g-3 align-items-end">
    <div class="col-md-7">
      <label class="form-label">Pilih Proyek</label>
      <select name="id_proyek" class="form-select" onchange="this.form.submit()">
        <?php foreach ($projects as $p): ?>
          <option value="<?= (int)$p['id_proyek'] ?>" <?= $selected === (int)$p['id_proyek'] ? 'selected' : '' ?>>
            <?= e($p['kode_proyek'] . ' - ' . $p['nama_proyek']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5">
      <a href="<?= build_url('/reports/index.php') ?>" class="btn btn-outline-primary">Lihat Laporan</a>
    </div>
  </form>
</div>

<div class="collapse show mb-4" id="formProgress">
  <div class="card-soft p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0"><?= $edit ? 'Edit Laporan Progres' : 'Tambah Laporan Progres' ?></h5>
      <button class="btn btn-sm btn-outline-secondary print-hidden" data-bs-toggle="collapse" data-bs-target="#formProgress">Sembunyikan</button>
    </div>
    <form method="post" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="id_progress" value="<?= e($edit['id_progress'] ?? '') ?>">
      <input type="hidden" name="id_proyek" value="<?= e($selected) ?>">
      <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal_laporan" class="form-control" value="<?= e($edit['tanggal_laporan'] ?? date('Y-m-d')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Progres (%)</label>
        <input type="number" name="progress_persen" class="form-control" min="0" max="100" step="0.1" value="<?= e($edit['progress_persen'] ?? 0) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Petugas</label>
        <input type="text" name="petugas" class="form-control" value="<?= e($edit['petugas'] ?? current_user()['nama_lengkap']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Dokumentasi</label>
        <input type="file" name="dokumentasi" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf">
      </div>
      <div class="col-12">
        <label class="form-label">Uraian Pekerjaan</label>
        <textarea name="uraian" class="form-control" rows="4" required><?= e($edit['uraian'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Simpan</button>
        <?php if ($edit): ?><a href="<?= build_url('/progress/index.php?id_proyek=' . $selected) ?>" class="btn btn-outline-secondary">Batal</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card-soft p-4">
  <h5 class="mb-3">Riwayat Progress</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Tanggal</th><th>Progres</th><th>Uraian</th><th>Petugas</th><th>Dokumentasi</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($history as $row): ?>
          <tr>
            <td><?= format_date($row['tanggal_laporan']) ?></td>
            <td><strong><?= number_format($row['progress_persen'], 1) ?>%</strong></td>
            <td><?= e($row['uraian']) ?></td>
            <td><?= e($row['petugas']) ?></td>
            <td>
              <?php if (!empty($row['dokumentasi'])): ?>
                <a href="<?= build_url('/uploads/' . rawurlencode($row['dokumentasi'])) ?>" target="_blank">Lihat File</a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td class="print-hidden">
              <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$row['id_progress'] ?>&id_proyek=<?= $selected ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$row['id_progress'] ?>&id_proyek=<?= $selected ?>" onclick="return confirm('Hapus laporan progres ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
