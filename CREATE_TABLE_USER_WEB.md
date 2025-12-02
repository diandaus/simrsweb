# Membuat Tabel user_web di Database Production

## 📋 Informasi
Tabel `user_web` diperlukan untuk menyimpan data user aplikasi SIMRS Khanza Web.

---

## 🚀 Cara 1: Menggunakan SQL Script (Recommended)

### Step 1: Login ke MySQL

```bash
mysql -u root -p nama_database_simrs_khanza
```

**Catatan:** Ganti `nama_database_simrs_khanza` dengan nama database SIMRS Khanza Anda.

### Step 2: Jalankan Script

```sql
-- Copy-paste script dari file create_user_web_table.sql
source /var/www/html/simrsweb/backend/create_user_web_table.sql;
```

**ATAU** copy-paste langsung:

```sql
CREATE TABLE IF NOT EXISTS `user_web` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `menu_permissions` json DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_web_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 3: Verifikasi

```sql
-- Cek struktur tabel
DESCRIBE user_web;

-- Atau
SHOW CREATE TABLE user_web;
```

---

## 🚀 Cara 2: Menggunakan Laravel Migration

### Step 1: Pastikan .env sudah dikonfigurasi

```bash
cd /var/www/html/simrsweb/backend
nano .env
```

Pastikan database sudah benar:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_simrs_khanza
DB_USERNAME=username_database
DB_PASSWORD=password_database
```

### Step 2: Jalankan Migration

```bash
cd /var/www/html/simrsweb/backend
php artisan migrate
```

Ini akan menjalankan semua migration, termasuk:
- `create_user_web_table`
- `add_menu_permissions_to_user_web_table`
- `add_foto_profil_to_user_web_table`

---

## ✅ Setelah Tabel Dibuat

### Buat User Admin

```bash
# Login ke MySQL
mysql -u root -p nama_database_simrs_khanza

# Jalankan script
source /var/www/html/simrsweb/backend/create_admin_user.sql;
```

**ATAU** via SQL langsung:

```sql
INSERT INTO `user_web` (
    `username`, 
    `password`, 
    `nama`, 
    `nip`, 
    `email`, 
    `role`, 
    `status`, 
    `created_at`, 
    `updated_at`
) VALUES (
    'admin',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', -- password: "admin123"
    'Administrator',
    '001',
    'admin@hospital.com',
    'admin',
    'active',
    NOW(),
    NOW()
);
```

**Login dengan:**
- Username: `admin`
- Password: `admin123`

---

## 🔍 Verifikasi Lengkap

```sql
-- Cek tabel ada
SHOW TABLES LIKE 'user_web';

-- Cek struktur
DESCRIBE user_web;

-- Cek data user
SELECT id, username, nama, role, status FROM user_web;
```

---

## ⚠️ Troubleshooting

### Error: Table already exists

Jika tabel sudah ada, gunakan:
```sql
DROP TABLE IF EXISTS user_web;
```
Lalu jalankan script create lagi.

### Error: Access denied

Pastikan user MySQL memiliki permission:
```sql
GRANT ALL PRIVILEGES ON nama_database.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

### Error: Unknown database

Pastikan database sudah dibuat:
```sql
CREATE DATABASE IF NOT EXISTS nama_database_simrs_khanza;
USE nama_database_simrs_khanza;
```

---

## 📝 Catatan Penting

1. **Jangan hapus tabel yang sudah ada** jika SIMRS Khanza Java juga menggunakannya
2. **Backup database** sebelum membuat tabel baru
3. **Password di-hash** menggunakan bcrypt (Laravel default)
4. **Kolom `menu_permissions`** menggunakan JSON untuk fleksibilitas

---

**Last Updated**: 2025-11-26

