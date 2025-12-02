# Panduan Migrasi ke Database Production

## 📋 Daftar Isi
1. [Persiapan Database Production](#persiapan-database-production)
2. [Konfigurasi Environment](#konfigurasi-environment)
3. [Migrasi Data User](#migrasi-data-user)
4. [Cara Login di Production](#cara-login-di-production)
5. [Troubleshooting](#troubleshooting)

---

## 🗄️ Persiapan Database Production

### 1. Pastikan Tabel `user_web` Ada di Production

Jalankan migration atau buat tabel manual:

```sql
-- Pastikan tabel user_web ada di database production
CREATE TABLE IF NOT EXISTS `user_web` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `menu_permissions` json DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_web_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Pastikan Kolom `menu_permissions` dan `foto_profil` Ada

```sql
-- Tambahkan kolom menu_permissions jika belum ada
ALTER TABLE `user_web` 
ADD COLUMN `menu_permissions` JSON NULL AFTER `status`;

-- Tambahkan kolom foto_profil jika belum ada
ALTER TABLE `user_web` 
ADD COLUMN `foto_profil` VARCHAR(255) NULL AFTER `menu_permissions`;
```

---

## ⚙️ Konfigurasi Environment

### 1. Update File `.env` di Backend

Edit file `backend/.env` dan sesuaikan dengan database production:

```env
# Database Production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_production
DB_USERNAME=username_production
DB_PASSWORD=password_production

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-production-domain.com

# Atau jika menggunakan IP
# APP_URL=http://192.168.1.100
```

### 2. Clear Cache Config

Setelah mengubah `.env`, jalankan:

```bash
cd backend
php artisan config:clear
php artisan cache:clear
```

---

## 👥 Migrasi Data User

### Opsi 1: Migrasi Data dari Dev ke Production (Jika Perlu)

**⚠️ PERINGATAN**: Hanya migrasikan jika diperlukan. Untuk keamanan, lebih baik buat user baru di production.

```sql
-- Export data user dari database dev
-- (Jalankan di database dev)
SELECT * FROM user_web;

-- Import ke production (Hati-hati dengan password!)
-- Password sudah di-hash, jadi bisa langsung copy
INSERT INTO user_web (username, password, nama, nip, email, no_telp, role, status, menu_permissions, foto_profil, created_at, updated_at)
VALUES 
  ('admin', '$2y$10$...', 'Admin', '001', 'admin@hospital.com', '081234567890', 'admin', 'active', NULL, NULL, NOW(), NOW()),
  -- ... user lainnya
;
```

### Opsi 2: Buat User Baru di Production (RECOMMENDED)

**Cara yang lebih aman adalah membuat user baru di production:**

#### A. Via SQL (Manual)

```sql
-- Buat user admin di production
INSERT INTO user_web (username, password, nama, nip, email, role, status, created_at, updated_at)
VALUES (
  'admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
  'Administrator',
  '001',
  'admin@hospital.com',
  'admin',
  'active',
  NOW(),
  NOW()
);
```

**⚠️ Catatan**: Password di atas adalah hash dari "password". Setelah login pertama kali, segera ubah password!

#### B. Via Aplikasi (Jika sudah ada user admin)

1. Login dengan user admin yang sudah ada
2. Buka **Settings** → **Manajemen User**
3. Klik **➕ Tambah User**
4. Isi form dan simpan

---

## 🔐 Cara Login di Production

### 1. Pastikan Backend Berjalan

```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

Atau jika menggunakan web server (Apache/Nginx), pastikan sudah dikonfigurasi.

### 2. Akses Halaman Login

Buka browser dan akses:
- `http://your-production-domain.com/login`
- Atau `http://192.168.1.100:8000/login` (jika menggunakan IP)

### 3. Login dengan Credentials

**Jika menggunakan user yang sudah ada:**
- **Username**: username yang sudah dibuat
- **Password**: password yang sudah di-set

**Jika baru membuat user via SQL:**
- **Username**: `admin`
- **Password**: `password` (segera ubah setelah login pertama!)

### 4. Setelah Login Berhasil

- User akan di-redirect ke halaman pertama yang diizinkan (berdasarkan `menu_permissions`)
- Admin akan di-redirect ke Dashboard
- User biasa akan di-redirect ke menu pertama yang diizinkan

---

## 🔄 Membuat User Baru di Production

### Via SQL

```sql
-- Generate password hash untuk password baru
-- Gunakan: php artisan tinker
-- Atau gunakan online bcrypt generator

-- Contoh: password "admin123"
-- Hash: $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy

INSERT INTO user_web (username, password, nama, nip, email, role, status, created_at, updated_at)
VALUES (
  'username_baru',
  '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', -- password: "admin123"
  'Nama Lengkap',
  'NIP123',
  'email@example.com',
  'admin', -- atau 'user'
  'active',
  NOW(),
  NOW()
);
```

### Via Laravel Tinker

```bash
cd backend
php artisan tinker
```

```php
use App\Models\UserWeb;
use Illuminate\Support\Facades\Hash;

// Buat user baru
$user = UserWeb::create([
    'username' => 'username_baru',
    'password' => Hash::make('password_baru'),
    'nama' => 'Nama Lengkap',
    'nip' => 'NIP123',
    'email' => 'email@example.com',
    'role' => 'admin',
    'status' => 'active',
]);

echo "User created: " . $user->username;
```

---

## 🛠️ Troubleshooting

### 1. Error: "SQLSTATE[HY000] [2002] Connection refused"

**Penyebab**: Database server tidak bisa diakses

**Solusi**:
- Pastikan MySQL/MariaDB berjalan
- Cek `DB_HOST` dan `DB_PORT` di `.env`
- Pastikan firewall mengizinkan koneksi

### 2. Error: "Access denied for user"

**Penyebab**: Username/password database salah

**Solusi**:
- Cek `DB_USERNAME` dan `DB_PASSWORD` di `.env`
- Pastikan user MySQL memiliki permission untuk database tersebut

### 3. Error: "Table 'user_web' doesn't exist"

**Penyebab**: Tabel belum dibuat di database production

**Solusi**:
- Jalankan migration: `php artisan migrate`
- Atau buat tabel manual menggunakan SQL di atas

### 4. User tidak bisa login

**Penyebab**: 
- Password salah
- User status tidak aktif
- User tidak ada di database

**Solusi**:
```sql
-- Cek user di database
SELECT id, username, nama, role, status FROM user_web;

-- Reset password user
UPDATE user_web 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- password: "password"
WHERE username = 'username_yang_ingin_direset';

-- Aktifkan user
UPDATE user_web 
SET status = 'active' 
WHERE username = 'username_yang_ingin_diaktifkan';
```

### 5. Foto profil tidak tampil

**Penyebab**: Symlink storage belum dibuat atau path salah

**Solusi**:
```bash
cd backend
php artisan storage:link
```

Pastikan folder `storage/app/public/foto_profil/` ada dan memiliki permission write.

---

## 📝 Checklist Migrasi ke Production

- [ ] Database production sudah dibuat
- [ ] Tabel `user_web` sudah ada di production
- [ ] Kolom `menu_permissions` dan `foto_profil` sudah ada
- [ ] File `.env` sudah dikonfigurasi dengan database production
- [ ] Cache config sudah di-clear
- [ ] User admin sudah dibuat di production
- [ ] Test login dengan user admin berhasil
- [ ] Symlink storage sudah dibuat (`php artisan storage:link`)
- [ ] Permission folder storage sudah benar
- [ ] Backend server berjalan dan bisa diakses

---

## 🔒 Keamanan Production

1. **Jangan expose `.env` file** - Pastikan `.env` tidak di-commit ke git
2. **Gunakan password yang kuat** untuk database dan user aplikasi
3. **Nonaktifkan `APP_DEBUG`** di production (`APP_DEBUG=false`)
4. **Gunakan HTTPS** jika memungkinkan
5. **Backup database** secara berkala
6. **Limit akses database** - hanya user yang diperlukan saja

---

## 📞 Support

Jika mengalami masalah, cek:
1. Log Laravel: `backend/storage/logs/laravel.log`
2. Console browser untuk error frontend
3. Network tab untuk error API

---

**Last Updated**: 2025-11-26

