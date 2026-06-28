<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php
if (!in_array(current_user()['role'] ?? '', ['admin', 'project_manager', 'compliance_officer'], true)) {
    die('Akses ditolak.');
}

$projects = get_projects();
$selected = (int)($_GET['id_proyek'] ?? ($projects[0]['id_proyek'] ?? 0));
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pemeriksaan = (int)($_POST['id_pemeriksaan'] ?? 0);
    $id_proyek = (int)($_POST['id_proyek'] ?? 0);
    $aspek = trim($_POST['aspek'] ?? '');
    $standar = trim($_POST['standar'] ?? '');
    $hasil = $_POST['status_hasil'] ?? 'Perlu Perbaikan';
    $catatan = trim($_POST['catatan'] ?? '');

    if ($id_pemeriksaan) {
        $stmt = $conn->prepare("UPDATE compliance_checks SET id_proyek=?, aspek=?, standar=?, status_hasil=?, catatan=?, updated_at=NOW() WHERE id_pemeriksaan=?");
        $stmt->bind_param("issssi", $id_proyek, $aspek, $standar, $hasil, $catatan, $id_pemeriksaan);
        $stmt->execute();
        flash('success', 'Pemeriksaan kepatuhan berhasil diperbarui.');
        record_log('UPDATE_KEPATUHAN', "Memperbarui pemeriksaan proyek ID {$id_proyek}");
    } else {
        $stmt = $conn->prepare("INSERT INTO compliance_checks (id_proyek, aspek, standar, status_hasil, catatan) VALUES (?,?,?,?,?)");
        $stmt->bind_param("issss", $id_proyek, $aspek, $standar, $hasil, $catatan);
        $stmt->execute();
        flash('success', 'Pemeriksaan kepatuhan berhasil ditambahkan.');
        record_log('TAMBAH_KEPATUHAN', "Menambah pemeriksaan proyek ID {$id_proyek}");
    }
    redirect('/compliance/index.php?id_proyek=' . $id_proyek);
}

if ($action === 'delete' && $id) {
    $row = fetch_one("SELECT id_proyek FROM compliance_checks WHERE id_pemeriksaan = {$id}");
    $stmt = $conn->prepare("DELETE FROM compliance_checks WHERE id_pemeriksaan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Data kepatuhan berhasil dihapus.');
    record_log('HAPUS_KEPATUHAN', "Menghapus pemeriksaan ID {$id}");
    redirect('/compliance/index.php?id_proyek=' . ($row['id_proyek'] ?? $selected));
}

$edit = ($action === 'edit' && $id) ? fetch_one("SELECT * FROM compliance_checks WHERE id_pemeriksaan = {$id}") : null;
if ($edit) $selected = (int)$edit['id_proyek'];
$history = $selected ? get_compliance_by_project($selected) : [];
?>
<div class="d-flex justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h2 class="mb-1">Kepatuhan Proyek</h2>
    <div class="small-muted">Validasi standar, regulasi, dan status kesesuaian pekerjaan.</div>
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
      <a href="<?= build_url('/reports/index.php') ?>" class="btn btn-outline-primary">Rekap Laporan</a>
    </div>
  </form>
</div>

<div class="card-soft p-4 mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><?= $edit ? 'Edit Pemeriksaan' : 'Input Pemeriksaan Baru' ?></h5>
    <button class="btn btn-sm btn-outline-secondary print-hidden" data-bs-toggle="collapse" data-bs-target="#formCompliance">Tampilkan/Sembunyikan</button>
  </div>
  <div class="collapse show" id="formCompliance">
    <form method="post" class="row g-3">
      <input type="hidden" name="id_pemeriksaan" value="<?= e($edit['id_pemeriksaan'] ?? '') ?>">
      <input type="hidden" name="id_proyek" value="<?= e($selected) ?>">
      <div class="col-md-4">
        <label class="form-label">Aspek</label>
        <input type="text" name="aspek" class="form-control" value="<?= e($edit['aspek'] ?? '') ?>" placeholder="Dokumentasi, K3, mutu, dll" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Standar / Acuan</label>
        <input type="text" name="standar" class="form-control" value="<?= e($edit['standar'] ?? '') ?>" placeholder="SNI, SOP, regulasi, dll" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Hasil</label>
        <select name="status_hasil" class="form-select">
          <?php foreach (['Memenuhi','Perlu Perbaikan','Tidak Memenuhi'] as $hasil): ?>
            <option value="<?= e($hasil) ?>" <?= (($edit['status_hasil'] ?? 'Perlu Perbaikan') === $hasil) ? 'selected' : '' ?>><?= e($hasil) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Catatan</label>
        <textarea name="catatan" class="form-control" rows="3"><?= e($edit['catatan'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Simpan</button>
        <?php if ($edit): ?><a href="<?= build_url('/compliance/index.php?id_proyek=' . $selected) ?>" class="btn btn-outline-secondary">Batal</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card-soft p-4">
  <h5 class="mb-3">Riwayat Kepatuhan</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Aspek</th><th>Standar</th><th>Hasil</th><th>Catatan</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($history as $row): ?>
        <tr>
          <td><?= e($row['aspek']) ?></td>
          <td><?= e($row['standar']) ?></td>
          <td><?= status_badge($row['status_hasil']) ?></td>
          <td><?= e($row['catatan']) ?></td>
          <td><?= format_date($row['created_at']) ?></td>
          <td class="print-hidden">
            <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$row['id_pemeriksaan'] ?>&id_proyek=<?= $selected ?>">Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$row['id_pemeriksaan'] ?>&id_proyek=<?= $selected ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
