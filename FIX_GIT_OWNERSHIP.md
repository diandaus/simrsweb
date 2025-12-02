# Fix Git Ownership Issue

## Masalah
Error: `fatal: detected dubious ownership in repository`

## Solusi

### Opsi 1: Tambahkan ke Safe Directory (Cepat)

```bash
git config --global --add safe.directory /var/www/html/simrsweb
```

### Opsi 2: Ubah Ownership (Recommended untuk Production)

```bash
# Set ownership ke www-data (user Apache)
sudo chown -R www-data:www-data /var/www/html/simrsweb

# Jika perlu pull sebagai root, tambahkan safe directory
sudo git config --global --add safe.directory /var/www/html/simrsweb
```

### Opsi 3: Clone Ulang dengan Ownership yang Benar

```bash
# Backup jika perlu
sudo cp -r /var/www/html/simrsweb /var/www/html/simrsweb-backup

# Hapus folder lama
sudo rm -rf /var/www/html/simrsweb

# Clone ulang
cd /var/www/html
sudo git clone https://github.com/diandaus/simrsweb.git

# Set ownership
sudo chown -R www-data:www-data /var/www/html/simrsweb
```

## Setelah Fix

Lanjutkan dengan:
```bash
cd /var/www/html/simrsweb
git pull origin main
```

