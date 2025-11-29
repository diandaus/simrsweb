# Panduan Deployment SIMRS Khanza Web ke Ubuntu Server dengan Apache

## 📋 Informasi Server
- **OS**: Ubuntu Server
- **Web Server**: Apache
- **Database**: MySQL/MariaDB (sama dengan SIMRS Khanza Java)
- **Aplikasi Existing**: SIMRS Khanza Java

---

## 🎯 Tujuan
Deploy aplikasi SIMRS Khanza Web ke server yang sama dengan SIMRS Khanza Java, menggunakan database yang sama.

---

## 📦 Prasyarat

### 1. Software yang Diperlukan

```bash
# Update sistem
sudo apt update && sudo apt upgrade -y

# Install PHP dan ekstensi yang diperlukan
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-sqlite3

# Install Composer (jika belum ada)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js dan npm (untuk build frontend)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Git (jika belum ada)
sudo apt install -y git
```

### 2. Verifikasi Database

Pastikan database SIMRS Khanza Java sudah berjalan dan bisa diakses:

```bash
# Test koneksi database
mysql -u root -p -e "SHOW DATABASES;"
```

Catat informasi database:
- **Database Name**: (misalnya: `rs_simrs` atau `simrs_khanza`)
- **Database User**: (misalnya: `root` atau user khusus)
- **Database Password**: (password untuk user database)

---

## 🚀 Langkah-langkah Deployment

### Step 1: Clone/Upload Aplikasi ke Server

#### Opsi A: Clone dari Git (jika menggunakan Git)

```bash
cd /var/www
sudo git clone <repository-url> simrs-web
sudo chown -R www-data:www-data simrs-web
```

#### Opsi B: Upload via SCP/SFTP

```bash
# Dari komputer lokal
scp -r simrs-web/ user@server-ip:/var/www/
```

Setelah upload, set permission:

```bash
sudo chown -R www-data:www-data /var/www/simrs-web
sudo chmod -R 755 /var/www/simrs-web
```

---

### Step 2: Setup Backend Laravel

```bash
cd /var/www/simrs-web/backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy file .env
cp .env.example .env

# Edit .env dengan database production
sudo nano .env
```

**Konfigurasi `.env` untuk Production:**

```env
APP_NAME="SIMRS Khanza Web"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-domain.com
# atau jika menggunakan IP: http://192.168.1.100

# Database (SAMA dengan SIMRS Khanza Java)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_simrs_khanza
DB_USERNAME=username_database
DB_PASSWORD=password_database

# Session & Cache
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

**Generate APP_KEY:**

```bash
php artisan key:generate
```

**Setup Storage:**

```bash
# Buat folder storage
php artisan storage:link

# Set permission
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Jalankan Migration (jika perlu):**

```bash
# Pastikan tabel user_web ada
php artisan migrate

# Atau buat manual (lihat create_admin_user.sql)
```

---

### Step 3: Setup Frontend

```bash
cd /var/www/simrs-web/frontend

# Install dependencies
npm install

# Build untuk production
npm run build

# Set permission
sudo chown -R www-data:www-data .
```

---

### Step 4: Konfigurasi Apache

#### A. Buat Virtual Host untuk Backend API

```bash
sudo nano /etc/apache2/sites-available/simrs-web-api.conf
```

**Isi file:**

```apache
<VirtualHost *:80>
    ServerName api.your-domain.com
    # atau gunakan IP: ServerName 192.168.1.100
    
    DocumentRoot /var/www/simrs-web/backend/public

    <Directory /var/www/simrs-web/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/simrs-web-api-error.log
    CustomLog ${APACHE_LOG_DIR}/simrs-web-api-access.log combined
</VirtualHost>
```

#### B. Buat Virtual Host untuk Frontend

```bash
sudo nano /etc/apache2/sites-available/simrs-web.conf
```

**Isi file:**

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    # atau gunakan IP: ServerName 192.168.1.100
    
    DocumentRoot /var/www/simrs-web/frontend/dist

    <Directory /var/www/simrs-web/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Fallback ke index.html untuk Vue Router
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/simrs-web-error.log
    CustomLog ${APACHE_LOG_DIR}/simrs-web-access.log combined
</VirtualHost>
```

#### C. Aktifkan Site dan Module

```bash
# Aktifkan modul yang diperlukan
sudo a2enmod rewrite
sudo a2enmod headers

# Aktifkan virtual host
sudo a2ensite simrs-web-api.conf
sudo a2ensite simrs-web.conf

# Test konfigurasi
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

### Step 5: Konfigurasi Frontend untuk Production

Edit file `frontend/src/api/axios.js`:

```javascript
const apiClient = axios.create({
  baseURL: 'http://api.your-domain.com/api',
  // atau jika menggunakan IP: baseURL: 'http://192.168.1.100/api'
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})
```

**Rebuild frontend setelah perubahan:**

```bash
cd /var/www/simrs-web/frontend
npm run build
```

---

### Step 6: Buat User Admin di Database

#### Opsi A: Via SQL (Recommended)

```bash
# Login ke MySQL
mysql -u root -p

# Pilih database SIMRS Khanza
USE nama_database_simrs_khanza;

# Jalankan script
source /var/www/simrs-web/backend/create_admin_user.sql;
```

#### Opsi B: Via Laravel Tinker

```bash
cd /var/www/simrs-web/backend
php artisan tinker
```

Lalu copy-paste kode dari `create_admin_via_tinker.php`.

---

### Step 7: Set Permission dan Security

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/simrs-web

# Set permission
sudo find /var/www/simrs-web -type d -exec chmod 755 {} \;
sudo find /var/www/simrs-web -type f -exec chmod 644 {} \;

# Permission khusus untuk storage dan cache
sudo chmod -R 775 /var/www/simrs-web/backend/storage
sudo chmod -R 775 /var/www/simrs-web/backend/bootstrap/cache

# Hapus .env dari public access (jika ada)
sudo chmod 600 /var/www/simrs-web/backend/.env
```

---

### Step 8: Setup SSL/HTTPS (Opsional tapi Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Generate SSL certificate
sudo certbot --apache -d your-domain.com -d api.your-domain.com
```

Setelah SSL aktif, update:
- `APP_URL` di `.env` menjadi `https://your-domain.com`
- `baseURL` di `frontend/src/api/axios.js` menjadi `https://api.your-domain.com/api`

---

## 🔧 Konfigurasi Tambahan

### 1. Setup Cron Job (jika diperlukan)

```bash
sudo crontab -e -u www-data
```

Tambahkan:

```cron
* * * * * cd /var/www/simrs-web/backend && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Setup Log Rotation

```bash
sudo nano /etc/logrotate.d/simrs-web
```

Isi:

```
/var/www/simrs-web/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 3. Optimize Laravel

```bash
cd /var/www/simrs-web/backend

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

---

## 🧪 Testing

### 1. Test Backend API

```bash
# Test endpoint
curl http://api.your-domain.com/api/me
# atau
curl http://192.168.1.100/api/me
```

### 2. Test Frontend

Buka browser:
- `http://your-domain.com`
- atau `http://192.168.1.100`

### 3. Test Login

- Username: `admin`
- Password: `admin123` (atau password yang dibuat)

---

## 🔄 Update Aplikasi

### Cara Update Aplikasi

```bash
# Backup dulu
sudo cp -r /var/www/simrs-web /var/www/simrs-web-backup-$(date +%Y%m%d)

# Pull update (jika menggunakan Git)
cd /var/www/simrs-web
sudo git pull

# Update backend
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update frontend
cd ../frontend
npm install
npm run build

# Set permission
sudo chown -R www-data:www-data /var/www/simrs-web
```

---

## 🛠️ Troubleshooting

### 1. Error 500 Internal Server Error

**Cek log:**

```bash
# Log Apache
sudo tail -f /var/log/apache2/error.log

# Log Laravel
sudo tail -f /var/www/simrs-web/backend/storage/logs/laravel.log
```

**Kemungkinan penyebab:**
- Permission salah
- `.env` tidak dikonfigurasi
- Database connection error

### 2. Error: "Class 'PDO' not found"

```bash
sudo apt install php8.2-pdo php8.2-pdo-mysql
sudo systemctl restart apache2
```

### 3. Error: "Storage link not found"

```bash
cd /var/www/simrs-web/backend
php artisan storage:link
sudo chown -R www-data:www-data storage
```

### 4. Frontend tidak bisa akses API (CORS Error)

Tambahkan di `backend/app/Http/Middleware/Cors.php` atau edit `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

Atau install package:

```bash
composer require fruitcake/laravel-cors
```

### 5. Database Connection Error

**Cek:**
- Database name, username, password di `.env` benar
- MySQL service berjalan: `sudo systemctl status mysql`
- User MySQL memiliki permission

```sql
-- Berikan permission ke user
GRANT ALL PRIVILEGES ON nama_database.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📝 Checklist Deployment

- [ ] PHP 8.2+ dan ekstensi terinstall
- [ ] Composer terinstall
- [ ] Node.js dan npm terinstall
- [ ] Aplikasi sudah di-upload ke `/var/www/simrs-web`
- [ ] File `.env` sudah dikonfigurasi dengan database production
- [ ] `APP_KEY` sudah di-generate
- [ ] Storage symlink sudah dibuat
- [ ] Permission folder sudah benar
- [ ] Virtual host Apache sudah dikonfigurasi
- [ ] Modul Apache (rewrite, headers) sudah aktif
- [ ] Frontend sudah di-build (`npm run build`)
- [ ] User admin sudah dibuat di database
- [ ] Test login berhasil
- [ ] SSL sudah di-setup (opsional)

---

## 🔒 Security Best Practices

1. **Jangan expose `.env` file**
   ```bash
   sudo chmod 600 /var/www/simrs-web/backend/.env
   ```

2. **Nonaktifkan directory listing**
   - Sudah diatur di virtual host dengan `Options -Indexes`

3. **Gunakan HTTPS**
   - Setup SSL dengan Certbot

4. **Firewall**
   ```bash
   sudo ufw allow 'Apache Full'
   sudo ufw enable
   ```

5. **Backup Database**
   ```bash
   # Buat script backup
   mysqldump -u root -p nama_database > backup_$(date +%Y%m%d).sql
   ```

---

## 📞 Support

Jika mengalami masalah:
1. Cek log Apache: `/var/log/apache2/error.log`
2. Cek log Laravel: `/var/www/simrs-web/backend/storage/logs/laravel.log`
3. Cek status service: `sudo systemctl status apache2`

---

**Last Updated**: 2025-11-26

