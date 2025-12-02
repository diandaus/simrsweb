# Verifikasi Password Hash

## Masalah: Password Salah Meskipun User Sudah Dibuat

Kemungkinan password hash tidak cocok. Mari verifikasi dan perbaiki.

## Step 1: Cek User di Database

```bash
mysql -u root -p ibnusina
```

```sql
-- Cek user admin
SELECT id, username, password, nama, role, status FROM user_web WHERE username = 'admin';

-- Lihat password hash yang tersimpan
SELECT username, LEFT(password, 20) as password_start FROM user_web WHERE username = 'admin';
```

## Step 2: Generate Hash Password Baru dengan Laravel

```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```

Di tinker, jalankan:
```php
use Illuminate\Support\Facades\Hash;
$hash = Hash::make('admin123');
echo $hash . "\n";
exit
```

**Copy hash yang dihasilkan!** (akan berbeda setiap kali, itu normal)

## Step 3: Update Password dengan Hash Baru

```bash
mysql -u root -p ibnusina
```

```sql
-- Ganti 'HASH_BARU_DARI_TINKER' dengan hash yang di-copy
UPDATE `user_web` 
SET 
    `password` = 'HASH_BARU_DARI_TINKER',
    `updated_at` = NOW()
WHERE `username` = 'admin';

-- Verifikasi
SELECT username, LEFT(password, 20) as password_start FROM user_web WHERE username = 'admin';
```

## Step 4: Test Hash Password

```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```

```php
use Illuminate\Support\Facades\Hash;
use App\Models\UserWeb;

$user = UserWeb::where('username', 'admin')->first();
$isValid = Hash::check('admin123', $user->password);
echo "Password valid: " . ($isValid ? 'YES' : 'NO') . "\n";
exit
```

Jika return `YES`, password sudah benar!

## Step 5: Test Login Lagi

```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

---

## Alternatif: Buat User Baru dengan Tinker

Jika masih bermasalah, buat user langsung via Laravel:

```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```

```php
use App\Models\UserWeb;
use Illuminate\Support\Facades\Hash;

$user = UserWeb::updateOrCreate(
    ['username' => 'admin'],
    [
        'password' => Hash::make('admin123'),
        'nama' => 'Administrator',
        'nip' => '001',
        'email' => 'admin@hospital.com',
        'role' => 'admin',
        'status' => 'active',
    ]
);

echo "User created/updated: " . $user->username . "\n";
exit
```

---

## Troubleshooting

### Cek apakah user ada:
```sql
SELECT COUNT(*) as count FROM user_web WHERE username = 'admin';
-- Harus return: 1
```

### Cek status user:
```sql
SELECT username, status FROM user_web WHERE username = 'admin';
-- Status harus: active
```

### Test password di Laravel:
```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```
```php
use App\Models\UserWeb;
$user = UserWeb::where('username', 'admin')->first();
if ($user) {
    echo "User found: " . $user->username . "\n";
    echo "Status: " . $user->status . "\n";
} else {
    echo "User not found!\n";
}
exit
```

---

## Catatan Penting

1. **Hash password berbeda setiap kali** - itu normal (bcrypt menggunakan salt)
2. **Pastikan menggunakan Hash::make()** dari Laravel, bukan hash manual
3. **Password harus di-hash dengan bcrypt** (default Laravel)
4. **User status harus 'active'** untuk bisa login

