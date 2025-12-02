# Install Node.js di Ubuntu Server

## Cara Install Node.js

Jalankan perintah berikut di server Ubuntu:

```bash
# Update package list
sudo apt update

# Install Node.js versi 20.x (LTS)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verifikasi instalasi
node --version
npm --version
```

## Atau Install via Snap (Alternatif)

```bash
sudo snap install node --classic
```

## Setelah Node.js Terinstall

Lanjutkan menjalankan script deployment:

```bash
cd /var/www/html/simrsweb/backend
sudo ./deploy.sh
```

---

## Troubleshooting

### Jika curl tidak ada:
```bash
sudo apt install -y curl
```

### Jika ada masalah permission:
```bash
# Set permission untuk npm global
mkdir ~/.npm-global
npm config set prefix '~/.npm-global'
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
```

