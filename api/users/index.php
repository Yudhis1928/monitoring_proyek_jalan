<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php role_guard(['admin']); ?>
<?php
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = (int)($_POST['id_user'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $role = $_POST['role'] ?? 'viewer';
    $password = $_POST['password'] ?? '';

    if ($id_user) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, nama_lengkap=?, role=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $nama, $role, $hash, $id_user);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, nama_lengkap=?, role=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $nama, $role, $id_user);
        }
        $stmt->execute();
        flash('success', 'Data pengguna berhasil diperbarui.');
        record_log('UPDATE_USER', "Memperbarui pengguna {$username}");
    } else {
        $hash = password_hash($password ?: 'password123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, nama_lengkap, role, password) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $username, $nama, $role, $hash);
        $stmt->execute();
        flash('success', 'Pengguna baru berhasil ditambahkan.');
        record_log('TAMBAH_USER', "Menambah pengguna {$username}");
    }
    redirect('/users/index.php');
}

if ($action === 'delete' && $id) {
    if ($id === (int)(current_user()['id'] ?? 0)) {
        die('Tidak bisa menghapus akun sendiri.');
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Pengguna berhasil dihapus.');
    record_log('HAPUS_USER', "Menghapus pengguna ID {$id}");
    redirect('/users/index.php');
}

$edit = ($action === 'edit' && $id) ? fetch_one("SELECT * FROM users WHERE id = {$id}") : null;
$data = get_users();
?>
<div class="d-flex justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h2 class="mb-1">Pengguna & RBAC</h2>
    <div class="small-muted">Atur akun dan peran akses. Karena tentu saja satu sistem harus dijaga oleh lebih dari satu orang.</div>
  </div>
  <button class="btn btn-primary print-hidden" data-bs-toggle="collapse" data-bs-target="#formUser">Tambah Pengguna</button>
</div>

<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-auto-dismiss><?= e($msg) ?></div><?php endif; ?>

<div class="collapse <?= $edit ? 'show' : '' ?> mb-4" id="formUser">
  <div class="card-soft p-4">
    <form method="post" class="row g-3">
      <input type="hidden" name="id_user" value="<?= e($edit['id'] ?? '') ?>">
      <div class="col-md-4">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= e($edit['username'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" class="form-control" value="<?= e($edit['nama_lengkap'] ?? '') ?>" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <?php foreach (['admin','project_manager','field_supervisor','compliance_officer','viewer'] as $role): ?>
            <option value="<?= e($role) ?>" <?= (($edit['role'] ?? 'viewer') === $role) ? 'selected' : '' ?>><?= e($role) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Password <?= $edit ? '(opsional)' : '' ?></label>
        <input type="password" name="password" class="form-control" <?= $edit ? '' : 'required' ?>>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Simpan</button>
        <?php if ($edit): ?><a href="<?= build_url('/users/index.php') ?>" class="btn btn-outline-secondary">Batal</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card-soft p-4">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Username</th><th>Nama</th><th>Role</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($data as $row): ?>
        <tr>
          <td><?= e($row['username']) ?></td>
          <td><?= e($row['nama_lengkap']) ?></td>
          <td><span class="badge text-bg-dark"><?= e($row['role']) ?></span></td>
          <td><?= format_date($row['created_at']) ?></td>
          <td class="print-hidden">
            <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$row['id'] ?>">Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
