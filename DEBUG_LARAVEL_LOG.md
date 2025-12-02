# Debug Laravel Log

## Cara Melihat Error Lengkap

### 1. Lihat Error Terbaru (20 baris terakhir)
```bash
sudo tail -20 /var/www/html/simrsweb/backend/storage/logs/laravel.log
```

### 2. Lihat Error Hari Ini
```bash
sudo grep "$(date +%Y-%m-%d)" /var/www/html/simrsweb/backend/storage/logs/laravel.log | tail -50
```

### 3. Cari Error Spesifik
```bash
# Cari error "Exception"
sudo grep -A 10 "Exception" /var/www/html/simrsweb/backend/storage/logs/laravel.log | tail -30

# Cari error "SQLSTATE"
sudo grep -A 10 "SQLSTATE" /var/www/html/simrsweb/backend/storage/logs/laravel.log | tail -30

# Cari error "NotFound"
sudo grep -A 10 "NotFound" /var/www/html/simrsweb/backend/storage/logs/laravel.log | tail -30
```

### 4. Monitor Log Real-time
```bash
sudo tail -f /var/www/html/simrsweb/backend/storage/logs/laravel.log
```

Lalu coba akses aplikasi di browser untuk melihat error yang muncul.

---

## Common Errors dan Solusi

### 1. Database Connection Error
**Error:** `SQLSTATE[HY000] [2002] Connection refused`
**Solusi:**
```bash
# Cek .env
cd /var/www/html/simrsweb/backend
nano .env

# Pastikan:
DB_HOST=127.0.0.1
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password

# Test koneksi
php artisan tinker
>>> DB::connection()->getPdo();
```

### 2. Table Not Found
**Error:** `SQLSTATE[42S02]: Base table or view not found`
**Solusi:**
```bash
# Pastikan tabel sudah dibuat
mysql -u root -p nama_database -e "SHOW TABLES LIKE 'user_web';"

# Jika belum, buat tabel
mysql -u root -p nama_database < /var/www/html/simrsweb/backend/create_user_web_table.sql
```

### 3. Permission Denied
**Error:** `Permission denied` atau `failed to open stream`
**Solusi:**
```bash
sudo chown -R www-data:www-data /var/www/html/simrsweb
sudo chmod -R 755 /var/www/html/simrsweb
sudo chmod -R 775 /var/www/html/simrsweb/backend/storage
sudo chmod -R 775 /var/www/html/simrsweb/backend/bootstrap/cache
```

### 4. Route Not Found
**Error:** `404 Not Found` atau `Route [xxx] not defined`
**Solusi:**
```bash
# Clear route cache
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

---

## Cek Status Aplikasi

### 1. Test Database Connection
```bash
cd /var/www/html/simrsweb/backend
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### 2. Test Route
```bash
php artisan route:list | grep login
```

### 3. Test API Endpoint
```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

---

## Clear All Cache
```bash
cd /var/www/html/simrsweb/backend
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

---

## Cek Permission
```bash
# Ownership
sudo chown -R www-data:www-data /var/www/html/simrsweb

# Permissions
sudo find /var/www/html/simrsweb -type d -exec chmod 755 {} \;
sudo find /var/www/html/simrsweb -type f -exec chmod 644 {} \;

# Storage & Cache
sudo chmod -R 775 /var/www/html/simrsweb/backend/storage
sudo chmod -R 775 /var/www/html/simrsweb/backend/bootstrap/cache
```

