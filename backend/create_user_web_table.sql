-- ============================================
-- SQL Script untuk Membuat Tabel user_web
-- Database Production SIMRS Khanza
-- ============================================
-- 
-- CARA PENGGUNAAN:
-- 1. Login ke MySQL: mysql -u root -p nama_database
-- 2. Jalankan script ini: source create_user_web_table.sql;
-- 
-- ATAU copy-paste langsung ke MySQL
--
-- ============================================

-- Buat tabel user_web
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

-- Verifikasi tabel sudah dibuat
SELECT 'Tabel user_web berhasil dibuat!' AS status;
DESCRIBE user_web;

