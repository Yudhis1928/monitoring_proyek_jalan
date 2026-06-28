<?php
require_once __DIR__ . '/../app/functions.php';

if (is_login()) {
    redirect('/admin/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role' => $user['role']
        ];
        record_log('LOGIN', 'Pengguna masuk ke sistem');
        redirect('/admin/dashboard.php');
    }
    $error = 'Username atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | <?= e(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= build_url('/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="container">
  <div class="row min-vh-100 align-items-center justify-content-center">
    <div class="col-md-5">
      <div class="card-soft p-4 p-md-5">
        <div class="mb-3">
          <div class="badge text-bg-primary mb-2">Dashboard Monitoring</div>
          <h3 class="mb-2">Masuk ke Sistem</h3>
          <p class="small-muted mb-0">Kelola proyek, progres, dan kepatuhan dari satu layar. Tentu saja, karena manusia senang membuat dashboard untuk mengawasi dashboard lainnya.</p>
        </div>
        <?php if ($error): ?>
          <div class="alert alert-danger" data-auto-dismiss><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 small text-muted">
          Default: <strong>admin</strong> / <strong>admin123</strong>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
