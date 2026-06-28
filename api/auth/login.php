<?php
// 1. Inisialisasi Sesi di baris paling atas sebelum output apa pun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Aktifkan Error Reporting untuk debugging di serverless environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Panggil file fungsi dengan jalur absolut (menggunakan __DIR__)
require_once __DIR__ . '/../app/functions.php';

// 4. Beri nilai cadangan jika APP_NAME belum terdefinisi di config/db
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Monitoring Jalan');
}

// 5. Cek status login, jika sudah masuk langsung lempar ke dashboard
if (is_login()) {
    redirect('/admin/dashboard.php');
}

$error = '';

// 6. Proses data ketika form dikirim (Method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Pastikan variabel koneksi $conn hasil jembatan di functions.php tersedia
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // Verifikasi kecocokan password terenkripsi
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role' => $user['role']
            ];
            
            // Catat log aktivitas masuk ke database
            record_log('LOGIN', 'Pengguna masuk ke sistem');
            
            // Alihkan ke halaman dashboard utama
            redirect('/admin/dashboard.php');
        }
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
      <div class="card-soft p-4 p-md-5" style="background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div class="mb-3">
          <div class="badge text-bg-primary mb-2">Dashboard Monitoring</div>
          <h3 class="mb-2">Masuk ke Sistem</h3>
          <p class="small text-muted mb-0">Kelola proyek, progres, dan kepatuhan dari satu layar. Tentu saja, karena manusia senang membuat dashboard untuk mengawasi dashboard lainnya.</p>
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
        
        <div class="mt-4 pt-2 small text-muted border-top">
          Default Akun: <strong>admin</strong> / <strong>admin123</strong>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
