<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php role_guard(['admin', 'project_manager']); ?>
<?php $logs = get_logs(100); ?>
<div class="d-flex justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h2 class="mb-1">Audit Log</h2>
    <div class="small-muted">Jejak aktivitas sistem. Karena kalau tidak dicatat, nanti semua orang akan pura-pura tidak tahu.</div>
  </div>
</div>

<div class="card-soft p-4">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Waktu</th><th>Pengguna</th><th>Role</th><th>Aksi</th><th>Detail</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $row): ?>
        <tr>
          <td><?= format_date($row['created_at']) ?></td>
          <td><?= e($row['nama_lengkap'] ?? 'System') ?></td>
          <td><?= e($row['role'] ?? '-') ?></td>
          <td><strong><?= e($row['aksi']) ?></strong></td>
          <td><?= e($row['detail']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
