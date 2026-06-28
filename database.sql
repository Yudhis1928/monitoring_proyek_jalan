CREATE DATABASE IF NOT EXISTS monitoring_proyek_scrum;
USE monitoring_proyek_scrum;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role ENUM('admin','project_manager','field_supervisor','compliance_officer','viewer') NOT NULL DEFAULT 'viewer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
  id_proyek INT AUTO_INCREMENT PRIMARY KEY,
  kode_proyek VARCHAR(20) NOT NULL UNIQUE,
  nama_proyek VARCHAR(150) NOT NULL,
  jenis_proyek ENUM('Jalan','Jembatan','Fasilitas Umum') NOT NULL,
  lokasi VARCHAR(150) NOT NULL,
  instansi VARCHAR(150) NOT NULL,
  nilai_anggaran DECIMAL(18,2) NOT NULL DEFAULT 0,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  persentase_progress DECIMAL(5,1) NOT NULL DEFAULT 0,
  status ENUM('Perencanaan','Berjalan','Terlambat','Selesai','Dibatalkan') NOT NULL DEFAULT 'Perencanaan',
  catatan TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE progress_reports (
  id_progress INT AUTO_INCREMENT PRIMARY KEY,
  id_proyek INT NOT NULL,
  tanggal_laporan DATE NOT NULL,
  progress_persen DECIMAL(5,1) NOT NULL DEFAULT 0,
  uraian TEXT NOT NULL,
  petugas VARCHAR(100) NOT NULL,
  dokumentasi VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_progress_project FOREIGN KEY (id_proyek) REFERENCES projects(id_proyek) ON DELETE CASCADE
);

CREATE TABLE compliance_checks (
  id_pemeriksaan INT AUTO_INCREMENT PRIMARY KEY,
  id_proyek INT NOT NULL,
  aspek VARCHAR(120) NOT NULL,
  standar VARCHAR(180) NOT NULL,
  status_hasil ENUM('Memenuhi','Perlu Perbaikan','Tidak Memenuhi') NOT NULL DEFAULT 'Perlu Perbaikan',
  catatan TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_compliance_project FOREIGN KEY (id_proyek) REFERENCES projects(id_proyek) ON DELETE CASCADE
);

CREATE TABLE activity_logs (
  id_log INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  aksi VARCHAR(80) NOT NULL,
  detail TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2y$12$iavgfEbcKSRcMgo5C1zc7etvTALirX3uY6rkxI7XlZM0iESSj1V2q', 'Administrator Sistem', 'admin'),
('pm01', '$2y$12$iavgfEbcKSRcMgo5C1zc7etvTALirX3uY6rkxI7XlZM0iESSj1V2q', 'Project Manager', 'project_manager'),
('field01', '$2y$12$iavgfEbcKSRcMgo5C1zc7etvTALirX3uY6rkxI7XlZM0iESSj1V2q', 'Pengawas Lapangan', 'field_supervisor'),
('compliance01', '$2y$12$iavgfEbcKSRcMgo5C1zc7etvTALirX3uY6rkxI7XlZM0iESSj1V2q', 'Petugas Kepatuhan', 'compliance_officer');

INSERT INTO projects (kode_proyek, nama_proyek, jenis_proyek, lokasi, instansi, nilai_anggaran, tanggal_mulai, tanggal_selesai, persentase_progress, status, catatan) VALUES
('PRJ-001', 'Perbaikan Jalan Utama', 'Jalan', 'Kecamatan A', 'Dinas PUPR', 2500000000, '2026-05-20', '2026-08-20', 35.0, 'Berjalan', 'Fokus pada drainase dan lapisan aspal'),
('PRJ-002', 'Rehabilitasi Jembatan Sungai Merah', 'Jembatan', 'Kelurahan B', 'Dinas PUPR', 4200000000, '2026-05-22', '2026-09-10', 22.5, 'Berjalan', 'Perlu perhatian pada pengecoran pondasi'),
('PRJ-003', 'Revitalisasi Lapangan dan Taman Kota', 'Fasilitas Umum', 'Pusat Kota', 'Dinas PUPR', 1800000000, '2026-05-25', '2026-07-30', 12.0, 'Berjalan', 'Dokumentasi progres harus diunggah rutin');

INSERT INTO progress_reports (id_proyek, tanggal_laporan, progress_persen, uraian, petugas, dokumentasi) VALUES
(1, '2026-06-10', 25.0, 'Pembersihan badan jalan dan perataan permukaan selesai.', 'Pengawas Lapangan 1', NULL),
(1, '2026-06-18', 35.0, 'Aspal dasar selesai dan siap masuk tahap finishing.', 'Pengawas Lapangan 1', NULL),
(2, '2026-06-16', 22.5, 'Pemasangan rangka awal selesai, cek mutu material dilakukan.', 'Pengawas Lapangan 2', NULL),
(3, '2026-06-17', 12.0, 'Pekerjaan pembongkaran lama dan penataan area selesai.', 'Pengawas Lapangan 3', NULL);

INSERT INTO compliance_checks (id_proyek, aspek, standar, status_hasil, catatan) VALUES
(1, 'Dokumentasi lapangan', 'SOP Pelaporan Harian', 'Memenuhi', 'Foto dan catatan sudah tersedia'),
(1, 'K3 kerja', 'Standar K3 Konstruksi', 'Perlu Perbaikan', 'APD perlu diperhatikan saat jam sibuk'),
(2, 'Mutu material', 'Spesifikasi teknis proyek', 'Memenuhi', 'Hasil uji material sesuai'),
(3, 'Perizinan lokasi', 'Dokumen administrasi proyek', 'Perlu Perbaikan', 'Surat dukungan warga belum lengkap');
