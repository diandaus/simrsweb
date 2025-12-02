# Konfigurasi Apache untuk PHP 8.2

## Masalah
PHP 8.2 sudah terinstall, tapi Apache belum dikonfigurasi untuk menggunakannya.

## Solusi

### Opsi 1: Menggunakan libapache2-mod-php8.2 (Recommended untuk Apache)

```bash
# Install modul PHP untuk Apache
sudo apt install -y libapache2-mod-php8.2

# Disable PHP versi lama (jika ada)
sudo a2dismod php8.1 2>/dev/null || true

# Enable PHP 8.2
sudo a2enmod php8.2

# Restart Apache
sudo systemctl restart apache2

# Verifikasi
php -v
```

### Opsi 2: Menggunakan PHP-FPM (Jika menggunakan FastCGI)

```bash
# Install PHP-FPM
sudo apt install -y php8.2-fpm

# Enable modul FastCGI
sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php8.2-fpm

# Restart Apache
sudo systemctl restart apache2
```

### Verifikasi Konfigurasi

Buat file test:

```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/phpinfo.php
```

Buka di browser: `http://server-ip/phpinfo.php`

Hapus file test setelah verifikasi:

```bash
sudo rm /var/www/html/phpinfo.php
```

---

## Troubleshooting

### Cek modul PHP yang aktif:

```bash
apache2ctl -M | grep php
```

### Cek konfigurasi PHP:

```bash
php -i | grep "Loaded Configuration File"
```

### Cek error log Apache:

```bash
sudo tail -f /var/log/apache2/error.log
```

---

## Setelah Konfigurasi

Lanjutkan deployment:

```bash
cd /var/www/html/simrsweb/backend
sudo ./deploy.sh
```

