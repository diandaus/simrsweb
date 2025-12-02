# Konfigurasi Hybrid Web Server SIMRS Khanza

## Deskripsi
Hybrid Web Server adalah server terpisah yang digunakan oleh SIMRS Khanza untuk menyimpan dan menyajikan berkas digital seperti:
- Gambar Radiologi
- Gambar Laboratorium PA (Patologi Anatomi)
- Dokumen medis lainnya

## Konfigurasi .env

Tambahkan konfigurasi berikut di file `.env`:

```env
# SIMRS Khanza Hybrid Web Server Configuration
# Server untuk mengakses berkas digital (gambar radiologi, lab PA, dll)
HYBRIDWEB_HOST=localhost
HYBRIDWEB_PORT=80
HYBRIDWEB_PATH=webapps
```

### Parameter:

- **HYBRIDWEB_HOST**: Hostname atau IP address server hybrid web
  - Default: `localhost`
  - Contoh: `192.168.1.100` atau `simrs-files.hospital.com`

- **HYBRIDWEB_PORT**: Port server hybrid web
  - Default: `80`
  - Jika menggunakan port 80, tidak akan ditambahkan ke URL
  - Contoh: `8080` akan menghasilkan URL `http://host:8080/...`

- **HYBRIDWEB_PATH**: Base path untuk webapps
  - Default: `webapps`
  - Sesuaikan dengan konfigurasi Tomcat/web server yang digunakan

## Contoh Konfigurasi

### 1. Server Lokal (Default)
```env
HYBRIDWEB_HOST=localhost
HYBRIDWEB_PORT=80
HYBRIDWEB_PATH=webapps
```
Menghasilkan URL: `http://localhost/webapps/labpa/gambar.jpg`

### 2. Server Terpisah dengan Port Custom
```env
HYBRIDWEB_HOST=192.168.1.100
HYBRIDWEB_PORT=8080
HYBRIDWEB_PATH=webapps
```
Menghasilkan URL: `http://192.168.1.100:8080/webapps/labpa/gambar.jpg`

### 3. Domain dengan HTTPS (akan diupdate nanti)
```env
HYBRIDWEB_HOST=files.hospital.com
HYBRIDWEB_PORT=443
HYBRIDWEB_PATH=simrs
```
Menghasilkan URL: `http://files.hospital.com:443/simrs/labpa/gambar.jpg`

## Struktur Folder di Hybrid Web Server

```
webapps/
├── labpa/          # Gambar laboratorium PA
│   ├── gambar1.jpg
│   └── gambar2.png
├── radiologi/      # Gambar radiologi
│   ├── xray1.jpg
│   └── ct-scan1.jpg
└── ... (folder lainnya)
```

## Penggunaan di Code

### Backend (Laravel)

Menggunakan helper `HybridWebHelper`:

```php
use App\Helpers\HybridWebHelper;

// Generate URL gambar Lab PA
$url = HybridWebHelper::getLabPAImageUrl('gambar.jpg');
// Output: http://localhost/webapps/labpa/gambar.jpg

// Generate URL gambar Radiologi
$url = HybridWebHelper::getRadiologiImageUrl('xray.jpg');
// Output: http://localhost/webapps/radiologi/xray.jpg

// Generate URL custom
$url = HybridWebHelper::getCustomFileUrl('dokumen', 'file.pdf');
// Output: http://localhost/webapps/dokumen/file.pdf
```

### Frontend (Vue)

URL sudah digenerate dari backend dan dikirim dalam response API:

```javascript
// Data dari API sudah berisi gambar_url
const item = {
  gambar: 'foto123.jpg',
  gambar_url: 'http://localhost/webapps/labpa/foto123.jpg'
}

// Tinggal gunakan di template
<img :src="item.gambar_url" alt="Gambar PA" />
```

## Troubleshooting

### 1. Gambar tidak muncul / Error 404
- Pastikan server Hybrid Web berjalan
- Periksa konfigurasi HYBRIDWEB_HOST dan HYBRIDWEB_PORT
- Pastikan file gambar ada di folder yang benar
- Periksa permission folder di server

### 2. CORS Error
Jika menggunakan server terpisah, tambahkan CORS header di web server:

**Apache (.htaccess)**:
```apache
Header set Access-Control-Allow-Origin "*"
```

**Nginx**:
```nginx
add_header Access-Control-Allow-Origin *;
```

### 3. Port tidak sesuai
Pastikan port di .env sesuai dengan konfigurasi Tomcat/web server Anda.
Cek di `server.xml` atau konfigurasi web server.

## Testing

Untuk test koneksi ke Hybrid Web Server:

1. Buka browser
2. Akses URL manual: `http://HYBRIDWEB_HOST:HYBRIDWEB_PORT/HYBRIDWEB_PATH/labpa/`
3. Pastikan folder dan file bisa diakses

## Update Configuration

Setelah mengubah file `.env`, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

## Keamanan

⚠️ **Catatan Keamanan**:
- Jangan expose Hybrid Web Server langsung ke internet
- Gunakan HTTPS untuk production
- Implementasikan autentikasi jika diperlukan
- Batasi akses file berdasarkan role user
