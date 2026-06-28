<?php
// 1. Definisikan BASE_URL secara dinamis untuk Vercel & Localhost
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . $domain);
}

// 2. Definisikan UPLOAD_DIR jika belum diatur di file lain
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../public/uploads'); 
}

// 3. Panggil db.php
require_once __DIR__ . '/db.php';

// 4. Jembatan MySQLi: Buat objek $conn berbasis MySQLi menggunakan kredensial dari db.php
if (!isset($conn) && defined('DB_HOST')) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Koneksi MySQLi Gagal: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function build_url($path = '') {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . build_url($path));
    exit;
}

function is_login() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function auth_guard() {
    if (!is_login()) {
        redirect('/auth/login.php');
    }
}

function role_guard(array $roles) {
    auth_guard();
    $role = current_user()['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        die('Akses ditolak. Humanitas memang suka membagi akses, lalu lupa siapa yang boleh masuk.');
    }
}

function flash($key, $message = null) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    if ($message === null) {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
}

function fetch_all($sql) {
    global $conn;
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_one($sql) {
    global $conn;
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

function count_rows($table, $where = '1=1') {
    $row = fetch_one("SELECT COUNT(*) AS total FROM {$table} WHERE {$where}");
    return (int)($row['total'] ?? 0);
}

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function format_date($date) {
    if (!$date) return '-';
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $date;
}

function status_badge($status) {
    $map = [
        'Perencanaan' => 'secondary',
        'Berjalan' => 'primary',
        'Terlambat' => 'warning',
        'Selesai' => 'success',
        'Dibatalkan' => 'danger',
        'Memenuhi' => 'success',
        'Perlu Perbaikan' => 'warning',
        'Tidak Memenuhi' => 'danger'
    ];
    $class = $map[$status] ?? 'dark';
    return '<span class="badge text-bg-' . $class . '">' . e($status) . '</span>';
}

function ensure_upload_dir() {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
}

function save_upload($field) {
    ensure_upload_dir();
    if (empty($_FILES[$field]['name'])) {
        return null;
    }
    if (!is_uploaded_file($_FILES[$field]['tmp_name'])) {
        return null;
    }
    $original = $_FILES[$field]['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','pdf'];
    if (!in_array($ext, $allowed, true)) {
        return null;
    }
    $name = uniqid('dok_', true) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        return $name;
    }
    return null;
}

function delete_upload($filename) {
    if (!$filename) return;
    $path = UPLOAD_DIR . '/' . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

function record_log($action, $detail = '') {
    global $conn;
    $user = current_user();
    $userId = $user['id'] ?? null;
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, aksi, detail) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $action, $detail);
    $stmt->execute();
}

function get_users() {
    return fetch_all("SELECT * FROM users ORDER BY id ASC");
}

function get_projects() {
    return fetch_all("SELECT * FROM projects ORDER BY id_proyek DESC");
}

function get_project_by_id($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id_proyek = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_project_progress($projectId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM progress_reports WHERE id_proyek = ? ORDER BY tanggal_laporan DESC, id_progress DESC");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_latest_progress($projectId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM progress_reports WHERE id_proyek = ? ORDER BY tanggal_laporan DESC, id_progress DESC LIMIT 1");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_compliance_by_project($projectId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM compliance_checks WHERE id_proyek = ? ORDER BY created_at DESC, id_pemeriksaan DESC");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_logs($limit = 50) {
    $limit = (int)$limit;
    return fetch_all("SELECT a.*, u.nama_lengkap, u.role FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC, a.id_log DESC LIMIT {$limit}");
}

function dashboard_metrics() {
    $projects = get_projects();
    $total = count($projects);
    $running = 0;
    $done = 0;
    $late = 0;
    $need_attention = 0;
    $avg_progress = 0;
    $compliance_score = 0;
    $compliance_count = 0;

    foreach ($projects as $p) {
        $status = $p['status'];
        if ($status === 'Berjalan') $running++;
        if ($status === 'Selesai') $done++;
        if ($status === 'Terlambat') $late++;

        $latest = get_latest_progress((int)$p['id_proyek']);
        $progress = (float)($latest['progress_persen'] ?? $p['persentase_progress'] ?? 0);
        $avg_progress += $progress;

        $expected = expected_progress($p);
        if ($expected - $progress > 15 && $status !== 'Selesai') {
            $need_attention++;
        }

        $checks = get_compliance_by_project((int)$p['id_proyek']);
        if ($checks) {
            $compliance_count++;
            $last = $checks[0]['status_hasil'] ?? 'Perlu Perbaikan';
            if ($last === 'Memenuhi') $compliance_score++;
        }
    }

    return [
        'total_projects' => $total,
        'running_projects' => $running,
        'completed_projects' => $done,
        'late_projects' => $late,
        'attention_projects' => $need_attention,
        'avg_progress' => $total ? round($avg_progress / $total, 1) : 0,
        'compliance_rate' => $compliance_count ? round(($compliance_score / $compliance_count) * 100, 1) : 0,
    ];
}

function expected_progress(array $project) {
    $start = strtotime($project['tanggal_mulai'] ?? '');
    $end = strtotime($project['tanggal_selesai'] ?? '');
    if (!$start || !$end || $end <= $start) return 0;
    $now = time();
    if ($now <= $start) return 0;
    if ($now >= $end) return 100;
    return round((($now - $start) / ($end - $start)) * 100, 1);
}

function recent_projects($limit = 5) {
    $limit = (int)$limit;
    return fetch_all("SELECT * FROM projects ORDER BY updated_at DESC, id_proyek DESC LIMIT {$limit}");
}

function recent_progress($limit = 5) {
    $limit = (int)$limit;
    return fetch_all("SELECT p.*, pr.kode_proyek, pr.nama_proyek FROM progress_reports p LEFT JOIN projects pr ON pr.id_proyek = p.id_proyek ORDER BY p.tanggal_laporan DESC, p.id_progress DESC LIMIT {$limit}");
}

function recent_compliance($limit = 5) {
    $limit = (int)$limit;
    return fetch_all("SELECT c.*, pr.kode_proyek, pr.nama_proyek FROM compliance_checks c LEFT JOIN projects pr ON pr.id_proyek = c.id_proyek ORDER BY c.created_at DESC, c.id_pemeriksaan DESC LIMIT {$limit}");
}
