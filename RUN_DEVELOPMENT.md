# Menjalankan Aplikasi di Development

## Error 404 di Development

Jika muncul error 404 pada endpoint API saat development, kemungkinan backend Laravel belum jalan.

## Cara Menjalankan Development

### 1. Backend Laravel (Terminal 1)

```bash
cd /Users/firdaus/simrs-web/backend
php artisan serve
```

Backend akan jalan di: `http://127.0.0.1:8000`

### 2. Frontend Vue (Terminal 2)

```bash
cd /Users/firdaus/simrs-web/frontend
npm run dev
```

Frontend akan jalan di: `http://localhost:5173`

### 3. Akses Aplikasi

Buka browser: `http://localhost:5173`

---

## Update Production

Jika sudah test di development dan ingin update production:

```bash
# Di server production
cd /var/www/html/simrsweb
git pull origin main

# Update backend (jika ada perubahan)
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Update frontend (jika ada perubahan)
cd ../frontend
npm install
npm run build

# Restart Apache
sudo systemctl restart apache2
```

---

## Troubleshooting Development

### Backend tidak jalan

```bash
# Cek apakah port 8000 sudah digunakan
lsof -ti:8000

# Jika sudah digunakan, kill process
kill -9 $(lsof -ti:8000)

# Atau gunakan port lain
php artisan serve --port=8001
```

### Frontend tidak bisa akses backend

Pastikan `frontend/src/api/axios.js` menggunakan URL yang benar:

```javascript
// Development
const baseURL = 'http://127.0.0.1:8000/api'

// Production
const baseURL = '/api'
```

### Database connection error

Pastikan `backend/.env` sudah dikonfigurasi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password
```

Test koneksi:
```bash
cd backend
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## Quick Start Development

```bash
# Terminal 1 - Backend
cd backend && php artisan serve

# Terminal 2 - Frontend
cd frontend && npm run dev
```

Lalu buka: `http://localhost:5173`

