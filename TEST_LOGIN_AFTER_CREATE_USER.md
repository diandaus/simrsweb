# Test Login Setelah Membuat User Admin

## User Admin Sudah Dibuat ✅

Query berhasil: `Query OK, 1 row affected`

## Test Login

### Via cURL:

```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

**PENTING:** Pastikan mengetik `curl` di awal command!

### Expected Response (Success):

```json
{
  "success": true,
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "username": "admin",
    "nama": "Administrator",
    "nip": "001",
    "email": "admin@hospital.com",
    "role": "admin",
    "status": "active"
  }
}
```

### Expected Response (Error):

```json
{
  "success": false,
  "message": "Password salah"
}
```

---

## Verifikasi User di Database

### Cek user yang sudah dibuat:

```bash
mysql -u root -p ibnusina
```

```sql
SELECT id, username, nama, role, status, created_at FROM user_web;
```

### Cek password hash:

```sql
SELECT username, password FROM user_web WHERE username = 'admin';
```

---

## Test di Browser

1. Buka browser: `http://192.168.1.220`
2. Masukkan:
   - **Username:** `admin`
   - **Password:** `admin123`
3. Klik Login

---

## Troubleshooting

### Jika masih "Password salah":

1. **Pastikan password hash benar:**
```sql
-- Di MySQL
SELECT username, password FROM user_web WHERE username = 'admin';
-- Pastikan hash sama dengan: $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy
```

2. **Reset password:**
```sql
UPDATE `user_web` 
SET 
    `password` = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    `updated_at` = NOW()
WHERE `username` = 'admin';
```

3. **Generate hash baru (jika perlu):**
```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
```
```php
use Illuminate\Support\Facades\Hash;
echo Hash::make('admin123');
exit
```

---

## Next Steps

Setelah login berhasil:
1. ✅ Test semua fitur aplikasi
2. ✅ Ubah password default (disarankan)
3. ✅ Buat user tambahan jika perlu

