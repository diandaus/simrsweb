# API Login - Konfigurasi Berhasil! ✅

## Status
Error berubah dari **404 Not Found** menjadi **405 Method Not Allowed** - ini berarti:
- ✅ Route sudah ditemukan
- ✅ Konfigurasi Apache sudah benar
- ✅ Laravel sudah bisa menerima request

## Penjelasan Error 405

Error 405 terjadi karena route `/api/login` hanya menerima method **POST**, bukan **GET**.

Ini adalah **perilaku yang benar** - endpoint login memang harus menggunakan POST untuk mengirim credentials.

## Test dengan Method POST

### Via cURL:
```bash
curl -X POST http://192.168.1.220/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

### Expected Response:
```json
{
  "success": true,
  "token": "1|xxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "username": "admin",
    "nama": "Administrator",
    ...
  }
}
```

### Via Browser/Postman:
- Method: **POST**
- URL: `http://192.168.1.220/api/login`
- Headers: `Content-Type: application/json`
- Body (JSON):
```json
{
  "username": "admin",
  "password": "admin123"
}
```

## Test Frontend

Buka browser: `http://192.168.1.220`

Frontend seharusnya sudah bisa mengakses API dengan benar karena:
- ✅ Route sudah ditemukan
- ✅ Konfigurasi axios sudah benar (menggunakan POST untuk login)

## Verifikasi Lengkap

### 1. Test Endpoint /api/me (GET - butuh auth)
```bash
# Tanpa token (harusnya 401)
curl http://192.168.1.220/api/me

# Dengan token (setelah login)
curl http://192.168.1.220/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 2. Test Health Check
```bash
curl http://192.168.1.220/api/up
```

### 3. Test Frontend
Buka: `http://192.168.1.220`
- Seharusnya menampilkan halaman login
- Coba login dengan username: `admin`, password: `admin123`

---

## Kesimpulan

✅ **Konfigurasi Apache sudah benar!**
✅ **Laravel routing sudah bekerja!**
✅ **API endpoint sudah bisa diakses!**

Error 405 adalah **normal** jika mengakses endpoint login dengan method GET. Gunakan POST untuk login.

---

## Next Steps

1. Test login dari frontend
2. Pastikan user admin sudah dibuat di database
3. Jika masih ada masalah, cek:
   - Database connection di `.env`
   - User admin sudah ada di tabel `user_web`
   - Password sudah benar

