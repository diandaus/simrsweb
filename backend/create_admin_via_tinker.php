<?php
/**
 * Script untuk membuat user admin via Laravel Tinker
 * 
 * CARA PENGGUNAAN:
 * 1. Pastikan sudah di folder backend
 * 2. Jalankan: php artisan tinker
 * 3. Copy-paste kode di bawah ke tinker
 * 
 * ATAU jalankan langsung:
 * php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use App\Models\UserWeb; use Illuminate\Support\Facades\Hash; \$user = UserWeb::create(['username' => 'admin', 'password' => Hash::make('admin123'), 'nama' => 'Administrator', 'nip' => '001', 'email' => 'admin@hospital.com', 'role' => 'admin', 'status' => 'active']); echo 'User created: ' . \$user->username;"
 */

use App\Models\UserWeb;
use Illuminate\Support\Facades\Hash;

// Buat user admin
$user = UserWeb::create([
    'username' => 'admin',
    'password' => Hash::make('admin123'), // Ganti dengan password yang diinginkan
    'nama' => 'Administrator',
    'nip' => '001',
    'email' => 'admin@hospital.com',
    'role' => 'admin',
    'status' => 'active',
    'menu_permissions' => null, // Admin memiliki akses ke semua menu
]);

echo "User created successfully!\n";
echo "Username: " . $user->username . "\n";
echo "Password: admin123 (SEGERA UBAH SETELAH LOGIN PERTAMA KALI!)\n";
