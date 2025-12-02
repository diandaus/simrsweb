-- Script untuk membuat user admin di database production
-- 
-- CARA PENGGUNAAN:
-- 1. Ganti 'username_admin' dengan username yang diinginkan
-- 2. Ganti 'password_admin' dengan password yang diinginkan (akan di-hash)
-- 3. Atau gunakan password default: "admin123"
-- 4. Jalankan script ini di database production
--
-- CATATAN:
-- - Password akan di-hash menggunakan bcrypt
-- - Untuk generate hash password, gunakan: php artisan tinker
--   lalu jalankan: Hash::make('password_anda')
-- - Atau gunakan online bcrypt generator: https://bcrypt-generator.com/

-- ============================================
-- OPSI 1: Buat user admin dengan password default "admin123"
-- ============================================
-- Hash untuk password "admin123": $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy

INSERT INTO `user_web` (
    `username`, 
    `password`, 
    `nama`, 
    `nip`, 
    `email`, 
    `role`, 
    `status`, 
    `menu_permissions`, 
    `foto_profil`, 
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
    NULL, -- Admin memiliki akses ke semua menu
    NULL,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE 
    `updated_at` = NOW();

-- ============================================
-- OPSI 2: Buat user biasa (non-admin)
-- ============================================
-- Uncomment baris di bawah jika ingin membuat user biasa

/*
INSERT INTO `user_web` (
    `username`, 
    `password`, 
    `nama`, 
    `nip`, 
    `email`, 
    `role`, 
    `status`, 
    `menu_permissions`, 
    `foto_profil`, 
    `created_at`, 
    `updated_at`
) VALUES (
    'user1',
    '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', -- password: "admin123"
    'User Biasa',
    '002',
    'user1@hospital.com',
    'user',
    'active',
    JSON_ARRAY('dashboard', 'pasien', 'jadwal-operasi'), -- Menu yang diizinkan
    NULL,
    NOW(),
    NOW()
);
*/

-- ============================================
-- VERIFIKASI: Cek user yang sudah dibuat
-- ============================================
SELECT 
    id,
    username,
    nama,
    nip,
    email,
    role,
    status,
    menu_permissions,
    created_at
FROM `user_web`
ORDER BY created_at DESC;

-- ============================================
-- RESET PASSWORD: Jika lupa password
-- ============================================
-- Uncomment dan ganti username jika perlu reset password

/*
UPDATE `user_web` 
SET 
    `password` = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', -- password: "admin123"
    `updated_at` = NOW()
WHERE `username` = 'admin';
*/

-- ============================================
-- AKTIFKAN USER: Jika user status inactive
-- ============================================
-- Uncomment dan ganti username jika perlu mengaktifkan user

/*
UPDATE `user_web` 
SET 
    `status` = 'active',
    `updated_at` = NOW()
WHERE `username` = 'admin';
*/

