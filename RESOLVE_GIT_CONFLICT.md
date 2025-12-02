# Resolve Git Conflict

## Masalah
Error: `Your local changes to the following files would be overwritten by merge`

## Solusi

### Opsi 1: Stash Perubahan Lokal (Recommended)

```bash
# Simpan perubahan lokal
git stash

# Pull update
git pull origin main

# Jika perlu perubahan lokal, restore dengan:
# git stash pop
```

### Opsi 2: Discard Perubahan Lokal (Jika tidak penting)

```bash
# Hapus perubahan lokal
git checkout -- backend/deploy.sh

# Pull update
git pull origin main
```

### Opsi 3: Reset Hard (Hati-hati! Hapus semua perubahan lokal)

```bash
# Reset semua perubahan lokal
git reset --hard origin/main

# Pull update
git pull origin main
```

## Setelah Resolve

Lanjutkan setup:
```bash
# Copy config Apache
sudo cp apache-vhost-production-ip.conf /etc/apache2/sites-available/simrs-web.conf
```

