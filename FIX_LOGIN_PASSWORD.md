# Fix Login: Password Salah

## Status
✅ API endpoint sudah bekerja!
❌ Password yang digunakan salah atau user belum dibuat

## Solusi

### Step 1: Cek User Admin di Database

```bash
# Login ke MySQL
mysql -u root -p nama_database_simrs_khanza

# Cek user admin
SELECT id, username, nama, role, status FROM user_web WHERE username = 'admin';
```

### Step 2: Buat User Admin (jika belum ada)

```bash
# Masih di MySQL
source /var/www/html/simrsweb/backend/create_admin_user.sql;
```

**ATAU** copy-paste SQL ini:

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
) ON DUPLICATE KEY UPDATE 
    `updated_at` = NOW();
```

### Step 3: Reset Password (jika user sudah ada)

```bash
# Login ke MySQL
mysql -u root -p nama_database_simrs_khanza
```

```sql
-- Reset password ke "admin123"
UPDATE `user_web` 
SET 
    `password` = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', -- password: "admin123"
    `updated_at` = NOW()
WHERE `username` = 'admin';
```

### Step 4: Generate Password Hash Baru (jika perlu)

Jika ingin password berbeda, generate hash baru:

```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```

Lalu di tinker:
```php
use Illuminate\Support\Facades\Hash;
echo Hash::make('password_anda');
exit
```

Copy hash yang dihasilkan, lalu update di database:
```sql
UPDATE `user_web` 
SET `password` = 'hash_yang_dihasilkan' 
WHERE `username` = 'admin';
```

### Step 5: Test Login Lagi

```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

**Expected Response:**
```json
{
  "success": true,
  "token": "1|xxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "username": "admin",
    "nama": "Administrator",
    ...
  }
}
```

---

## Verifikasi User

### Cek semua user:
```sql
SELECT id, username, nama, role, status, created_at FROM user_web;
```

### Cek user aktif:
```sql
SELECT id, username, nama, role FROM user_web WHERE status = 'active';
```

---

## Troubleshooting

### Jika masih "Password salah":

1. **Pastikan password hash benar:**
```sql
SELECT username, password FROM user_web WHERE username = 'admin';
```

2. **Test hash password:**
```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```
```php
use Illuminate\Support\Facades\Hash;
Hash::check('admin123', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy');
// Seharusnya return: true
```

3. **Cek apakah user status active:**
```sql
SELECT username, status FROM user_web WHERE username = 'admin';
-- Status harus 'active'
```

---

## Password Default

- **Username:** `admin`
- **Password:** `admin123`
- **Hash:** `$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy`

**PENTING:** Segera ubah password setelah login pertama kali!

