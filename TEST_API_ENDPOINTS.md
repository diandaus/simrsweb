# Test API Endpoints

## Route Sudah Terdaftar ✅

Route `POST api/login` sudah terdaftar di Laravel. Sekarang test apakah bisa diakses.

## Test Endpoints

### 1. Test Endpoint Login

```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' \
  -v
```

**Expected Response:**
- Success: `{"success":true,"token":"...","user":{...}}`
- Error 404: Masalah konfigurasi Apache
- Error 401: Credentials salah (tapi endpoint bisa diakses)

### 2. Test Endpoint /api/me (tanpa auth)

```bash
curl http://192.168.1.220/api/me -v
```

**Expected Response:**
- Error 401: Normal (endpoint bisa diakses, hanya perlu auth)
- Error 404: Masalah konfigurasi Apache

### 3. Test Endpoint Health Check

```bash
curl http://192.168.1.220/api/up -v
```

**Expected Response:**
- `{"status":"ok"}` atau `{"status":"up"}`

### 4. Test Index.php Langsung

```bash
curl http://192.168.1.220/api/index.php -v
```

Jika ini juga 404, berarti masalah di konfigurasi Apache Alias.

---

## Jika Masih 404

### Cek Konfigurasi Apache

```bash
# Cek file konfigurasi
sudo cat /etc/apache2/sites-available/simrs-web.conf | grep -A 20 "Alias /api"

# Cek apakah mod_rewrite aktif
apache2ctl -M | grep rewrite

# Cek error log
sudo tail -30 /var/log/apache2/error.log
```

### Alternatif: Test dengan Path Langsung

```bash
# Test apakah file index.php bisa diakses
curl http://192.168.1.220/api/index.php

# Test apakah folder public bisa diakses
curl http://192.168.1.220/api/
```

---

## Debugging Steps

1. **Pastikan Apache sudah restart:**
```bash
sudo systemctl restart apache2
sudo systemctl status apache2
```

2. **Cek apakah site sudah aktif:**
```bash
sudo a2ensite simrs-web.conf
sudo systemctl restart apache2
```

3. **Cek permission:**
```bash
ls -la /var/www/html/simrsweb/backend/public/index.php
ls -la /var/www/html/simrsweb/backend/public/.htaccess
```

4. **Test dengan IP langsung:**
```bash
# Jika menggunakan IP, pastikan ServerName benar
curl -H "Host: 192.168.1.220" http://192.168.1.220/api/login -v
```

---

## Solusi Alternatif: Gunakan DocumentRoot ke Backend

Jika Alias tidak bekerja, gunakan konfigurasi ini:

```apache
<VirtualHost *:80>
    ServerName 192.168.1.220
    
    # Backend sebagai DocumentRoot
    DocumentRoot /var/www/html/simrsweb/backend/public
    
    <Directory /var/www/html/simrsweb/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Frontend di path /app
    Alias /app /var/www/html/simrsweb/frontend/dist
    <Directory /var/www/html/simrsweb/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /app/index.html [L]
    </Directory>
</VirtualHost>
```

Lalu update `frontend/src/api/axios.js`:
```javascript
const baseURL = import.meta.env.VITE_API_URL || '/api'
```

Dan rebuild frontend.

