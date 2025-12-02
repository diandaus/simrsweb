# Quick Fix Steps untuk Error 404 API

## Setelah Konfigurasi Apache OK

### Step 1: Restart Apache

```bash
sudo systemctl restart apache2
```

### Step 2: Clear Laravel Cache

```bash
cd /var/www/html/simrsweb/backend
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Step 3: Verifikasi Route

```bash
php artisan route:list | grep -E "(login|api)"
```

### Step 4: Test API Endpoint

```bash
# Test endpoint login
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

### Step 5: Cek Error Log (jika masih error)

```bash
# Apache error log
sudo tail -20 /var/log/apache2/error.log

# Laravel log
sudo tail -20 /var/www/html/simrsweb/backend/storage/logs/laravel.log
```

---

## Troubleshooting

### Jika masih 404:

1. **Pastikan mod_rewrite aktif:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. **Cek file .htaccess ada:**
```bash
ls -la /var/www/html/simrsweb/backend/public/.htaccess
```

3. **Cek permission:**
```bash
sudo chmod 644 /var/www/html/simrsweb/backend/public/.htaccess
```

4. **Test langsung ke index.php:**
```bash
curl http://192.168.1.220/api/index.php
```

---

## Verifikasi Final

Buka browser dan test:
- Frontend: `http://192.168.1.220`
- API Test: `http://192.168.1.220/api/me` (harusnya 401 Unauthorized, bukan 404)

