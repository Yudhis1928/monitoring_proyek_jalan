# Sistem Monitoring Proyek Jalan, Jembatan, dan Fasilitas Umum

Aplikasi web PHP native dengan Bootstrap untuk:
- Login dan RBAC
- Dashboard monitoring
- CRUD data proyek
- Input progres lapangan + upload dokumentasi
- Pemeriksaan kepatuhan
- Laporan rekap
- Pengguna & audit log

## Kebutuhan
- PHP 8+
- MySQL / MariaDB
- Extension mysqli aktif

## Langkah instalasi
1. Import `database.sql` ke MySQL.
2. Ubah konfigurasi di `app/config.php` bila nama database, user, password, atau folder project berbeda.
3. Pastikan folder `uploads/` bisa ditulis.
4. Buka `auth/login.php` atau root project.

## Login default
- Username: `admin`
- Password: `admin123`

## Role contoh
- admin
- project_manager
- field_supervisor
- compliance_officer
- viewer
