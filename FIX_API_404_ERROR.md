# Fix Error 404 pada /api/login

## Masalah
Error: `The route login could not be found` saat mengakses `/api/login`

## Penyebab
Konfigurasi Apache dengan `Alias /api` menyebabkan Laravel menerima path tanpa prefix `/api`, sedangkan route API membutuhkan prefix.

## Solusi

### Step 1: Update Konfigurasi Apache

Pull update terbaru dari GitHub:

```bash
cd /var/www/html/simrsweb
git pull origin main
```

### Step 2: Update File Apache Virtual Host

```bash
sudo cp /var/www/html/simrsweb/apache-vhost-production-ip.conf /etc/apache2/sites-available/simrs-web.conf
```

### Step 3: Test dan Restart Apache

```bash
# Test konfigurasi
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### Step 4: Clear Laravel Cache

```bash
cd /var/www/html/simrsweb/backend
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Step 5: Test API

```bash
# Test endpoint
curl http://192.168.1.220/api/me
```

---

## Alternatif: Jika Masih Error

Jika masih error, coba konfigurasi ini di `/etc/apache2/sites-available/simrs-web.conf`:

```apache
<VirtualHost *:80>
    ServerName 192.168.1.220
    ServerAlias localhost
    
    DocumentRoot /var/www/html/simrsweb/frontend/dist

    <Directory /var/www/html/simrsweb/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Backend API
    Alias /api /var/www/html/simrsweb/backend/public
    
    <Directory /var/www/html/simrsweb/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <IfModule mod_rewrite.c>
        RewriteEngine On
        
        # API routes
        RewriteCond %{REQUEST_URI} ^/api
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^api/(.*)$ /api/index.php [L]
        
        # Frontend routes
        RewriteCond %{REQUEST_URI} !^/api
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ /index.html [L]
    </IfModule>
</VirtualHost>
```

---

## Verifikasi

1. **Cek route Laravel:**
```bash
cd /var/www/html/simrsweb/backend
php artisan route:list | grep login
```

2. **Test dengan curl:**
```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

3. **Cek error log:**
```bash
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/www/html/simrsweb/backend/storage/logs/laravel.log
```

---

## Catatan

- Pastikan mod_rewrite sudah aktif: `sudo a2enmod rewrite`
- Pastikan file `.htaccess` ada di `backend/public/.htaccess`
- Pastikan permission folder sudah benar

