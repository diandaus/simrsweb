-- ============================================
-- SQL Script untuk Membuat Tabel personal_access_tokens
-- Database Production SIMRS Khanza
-- ============================================
-- 
-- Tabel ini diperlukan untuk Laravel Sanctum (API authentication)
-- 
-- CARA PENGGUNAAN:
-- 1. Login ke MySQL: mysql -u root -p nama_database
-- 2. Jalankan script ini: source create_personal_access_tokens_table.sql;
-- 
-- ATAU copy-paste langsung ke MySQL
--
-- ============================================

-- Buat tabel personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verifikasi tabel sudah dibuat
SELECT 'Tabel personal_access_tokens berhasil dibuat!' AS status;
DESCRIBE personal_access_tokens;

