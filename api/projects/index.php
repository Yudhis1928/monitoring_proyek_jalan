<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php
if (!in_array(current_user()['role'] ?? '', ['admin', 'project_manager'], true)) {
    die('Akses ditolak.');
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proyek = (int)($_POST['id_proyek'] ?? 0);
    $kode = trim($_POST['kode_proyek'] ?? '');
    $nama = trim($_POST['nama_proyek'] ?? '');
    $jenis = trim($_POST['jenis_proyek'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $instansi = trim($_POST['instansi'] ?? '');
    $anggaran = (float)($_POST['nilai_anggaran'] ?? 0);
    $tgl_mulai = $_POST['tanggal_mulai'] ?? null;
    $tgl_selesai = $_POST['tanggal_selesai'] ?? null;
    $progress = (float)($_POST['persentase_progress'] ?? 0);
    $status = $_POST['status'] ?? 'Perencanaan';
    $catatan = trim($_POST['catatan'] ?? '');

    if ($id_proyek) {
        $stmt = $conn->prepare("UPDATE projects SET kode_proyek=?, nama_proyek=?, jenis_proyek=?, lokasi=?, instansi=?, nilai_anggaran=?, tanggal_mulai=?, tanggal_selesai=?, persentase_progress=?, status=?, catatan=?, updated_at=NOW() WHERE id_proyek=?");
        $stmt->bind_param("sssssdssdssi", $kode, $nama, $jenis, $lokasi, $instansi, $anggaran, $tgl_mulai, $tgl_selesai, $progress, $status, $catatan, $id_proyek);
        $stmt->execute();
        flash('success', 'Data proyek berhasil diperbarui.');
        record_log('UPDATE_PROYEK', "Memperbarui data proyek {$kode}");
    } else {
        $stmt = $conn->prepare("INSERT INTO projects (kode_proyek, nama_proyek, jenis_proyek, lokasi, instansi, nilai_anggaran, tanggal_mulai, tanggal_selesai, persentase_progress, status, catatan) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssdssdss", $kode, $nama, $jenis, $lokasi, $instansi, $anggaran, $tgl_mulai, $tgl_selesai, $progress, $status, $catatan);
        $stmt->execute();
        flash('success', 'Data proyek berhasil ditambahkan.');
        record_log('TAMBAH_PROYEK', "Menambahkan data proyek {$kode}");
    }
    redirect('/projects/index.php');
}

if ($action === 'delete' && $id) {
    $stmt = $conn->prepare("DELETE FROM projects WHERE id_proyek = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Data proyek berhasil dihapus.');
    record_log('HAPUS_PROYEK', "Menghapus proyek ID {$id}");
    redirect('/projects/index.php');
}

$edit = ($action === 'edit' && $id) ? get_project_by_id($id) : null;
$data = get_projects();
?>
<div class="d-flex justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h2 class="mb-1">Data Proyek</h2>
    <div class="small-muted">Kelola proyek jalan, jembatan, dan fasilitas umum.</div>
  </div>
  <button class="btn btn-primary print-hidden" data-bs-toggle="collapse" data-bs-target="#formProject">Tambah Proyek</button>
</div>

<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-auto-dismiss><?= e($msg) ?></div><?php endif; ?>

<div class="collapse <?= $edit ? 'show' : '' ?> mb-4" id="formProject">
  <div class="card-soft p-4">
    <form method="post" class="row g-3">
      <input type="hidden" name="id_proyek" value="<?= e($edit['id_proyek'] ?? '') ?>">
      <div class="col-md-3">
        <label class="form-label">Kode Proyek</label>
        <input type="text" name="kode_proyek" class="form-control" value="<?= e($edit['kode_proyek'] ?? '') ?>" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Nama Proyek</label>
        <input type="text" name="nama_proyek" class="form-control" value="<?= e($edit['nama_proyek'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Jenis Proyek</label>
        <select name="jenis_proyek" class="form-select" required>
          <?php foreach (['Jalan','Jembatan','Fasilitas Umum'] as $jenis): ?>
            <option value="<?= e($jenis) ?>" <?= (($edit['jenis_proyek'] ?? '') === $jenis) ? 'selected' : '' ?>><?= e($jenis) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Lokasi</label>
        <input type="text" name="lokasi" class="form-control" value="<?= e($edit['lokasi'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Instansi / Pelaksana</label>
        <input type="text" name="instansi" class="form-control" value="<?= e($edit['instansi'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Anggaran</label>
        <input type="number" name="nilai_anggaran" class="form-control" step="1000" value="<?= e($edit['nilai_anggaran'] ?? 0) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai" class="form-control" value="<?= e($edit['tanggal_mulai'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tanggal Selesai</label>
        <input type="date" name="tanggal_selesai" class="form-control" value="<?= e($edit['tanggal_selesai'] ?? '') ?>" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Progres (%)</label>
        <input type="number" name="persentase_progress" class="form-control" min="0" max="100" step="0.1" value="<?= e($edit['persentase_progress'] ?? 0) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <?php foreach (['Perencanaan','Berjalan','Terlambat','Selesai','Dibatalkan'] as $status): ?>
            <option value="<?= e($status) ?>" <?= (($edit['status'] ?? 'Perencanaan') === $status) ? 'selected' : '' ?>><?= e($status) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Catatan</label>
        <textarea name="catatan" class="form-control" rows="3"><?= e($edit['catatan'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Simpan</button>
        <a href="<?= build_url('/projects/index.php') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<div class="card-soft p-4">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Kode</th><th>Proyek</th><th>Lokasi</th><th>Waktu</th><th>Anggaran</th><th>Progres</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $row): ?>
          <tr>
            <td><?= e($row['kode_proyek']) ?></td>
            <td>
              <div class="fw-semibold"><?= e($row['nama_proyek']) ?></div>
              <div class="small text-muted"><?= e($row['jenis_proyek']) ?> · <?= e($row['instansi']) ?></div>
            </td>
            <td><?= e($row['lokasi']) ?></td>
            <td><?= format_date($row['tanggal_mulai']) ?> s.d. <?= format_date($row['tanggal_selesai']) ?></td>
            <td><?= rupiah($row['nilai_anggaran']) ?></td>
            <td><?= number_format($row['persentase_progress'], 1) ?>%</td>
            <td><?= status_badge($row['status']) ?></td>
            <td class="print-hidden">
              <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$row['id_proyek'] ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$row['id_proyek'] ?>" onclick="return confirm('Hapus data proyek ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
