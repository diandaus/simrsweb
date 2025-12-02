# Upgrade PHP ke 8.2+ di Ubuntu Server

## Masalah
Server menggunakan PHP 8.1.2, tetapi aplikasi Laravel membutuhkan PHP 8.2+.

## Solusi: Install PHP 8.2

### Step 1: Tambahkan Repository PHP

```bash
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### Step 2: Install PHP 8.2 dan Ekstensi

```bash
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-sqlite3
```

### Step 3: Set PHP 8.2 sebagai Default

```bash
# Set PHP 8.2 sebagai default CLI
sudo update-alternatives --set php /usr/bin/php8.2

# Untuk Apache, enable PHP 8.2 FPM
sudo a2enmod php8.2
sudo a2dismod php8.1  # Disable PHP 8.1 jika ada
sudo systemctl restart apache2
```

### Step 4: Verifikasi

```bash
php -v
```

Seharusnya menampilkan: `PHP 8.2.x`

### Step 5: Lanjutkan Deployment

```bash
cd /var/www/html/simrsweb/backend
sudo ./deploy.sh
```

---

## Alternatif: Install PHP 8.3 (Latest)

Jika ingin menggunakan PHP 8.3:

```bash
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml \
    php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath \
    php8.3-intl php8.3-sqlite3

sudo update-alternatives --set php /usr/bin/php8.3
sudo a2enmod php8.3
sudo systemctl restart apache2
```

---

## Troubleshooting

### Jika ada konflik dengan PHP lama:

```bash
# Cek versi PHP yang terinstall
php -v
ls -la /usr/bin/php*

# Hapus PHP 8.1 (opsional, jika tidak digunakan)
sudo apt remove php8.1* --purge
sudo apt autoremove
```

### Jika Apache tidak restart:

```bash
# Cek error
sudo systemctl status apache2
sudo tail -f /var/log/apache2/error.log

# Test konfigurasi
sudo apache2ctl configtest
```

---

## Catatan Penting

- **Jangan hapus PHP 8.1** jika masih digunakan oleh aplikasi lain (seperti SIMRS Khanza Java)
- PHP 8.2 dan 8.1 bisa **berjalan bersamaan** di server yang sama
- Pastikan **Apache dikonfigurasi** untuk menggunakan PHP 8.2 untuk aplikasi web ini

