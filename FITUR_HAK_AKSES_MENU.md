# Fitur Hak Akses Menu User

## Deskripsi
Fitur ini memungkinkan admin untuk mengatur menu mana saja yang dapat diakses oleh setiap user. Cocok untuk membatasi akses user tertentu hanya ke modul tertentu (misalnya hanya Farmasi saja).

## Cara Menggunakan

### 1. Akses Pengaturan
- Login sebagai **Admin**
- Klik icon ⚙️ **Setting** di sidebar bawah
- Pilih tab **🔐 Hak Akses Menu**

### 2. Kelola Hak Akses User
- Akan muncul daftar semua user
- Klik tombol **🔐 Kelola** pada user yang ingin diatur
- Centang menu-menu yang ingin diberikan akses
- Klik **Simpan Hak Akses**

### 3. Menu yang Tersedia
- 📊 Dashboard
- 👥 Daftar Pasien
- 🩺 Rawat Jalan (IGD & Poli)
- 🛏️ Rawat Inap (Belum Pulang & Pulang)
- 🔬 Laboratorium (Permintaan & Periksa PK/PA)
- 🩻 Radiologi (Permintaan & Periksa)
- 🏥 Jadwal Operasi
- 💊 Farmasi

## Contoh Penggunaan

### Kasus 1: User Khusus Farmasi
1. Buat user baru dengan username "farmasi01"
2. Set role = "user"
3. Masuk ke tab Hak Akses Menu
4. Pilih user "farmasi01"
5. Centang hanya menu **Farmasi**
6. Simpan

**Hasil**: 
- User farmasi01 hanya akan melihat menu Farmasi di sidebar
- Saat login, **langsung diarahkan ke halaman Farmasi** (bukan Dashboard)
- Jika mencoba akses URL lain (misal `/dashboard`), akan otomatis redirect ke Farmasi

### Kasus 2: User Laboratorium
1. Pilih user yang ingin dibatasi
2. Centang menu:
   - Dashboard
   - Daftar Pasien
   - Laboratorium
3. Simpan

**Hasil**: User hanya bisa akses Dashboard, Daftar Pasien, dan Laboratorium

## Catatan Penting

### Role Admin
- User dengan role **admin** memiliki akses ke **semua menu** secara otomatis
- Hak akses menu tidak dapat diubah untuk admin
- Hanya digunakan untuk user dengan role **user**

### Backward Compatibility
- Jika user belum diset hak aksesnya (menu_permissions = null/empty), maka akan mendapat akses ke semua menu
- Ini memastikan user lama tetap bisa mengakses semua fitur

### Submenu
- Jika parent menu dicentang (misal: Rawat Jalan), maka semua submenu di dalamnya otomatis dapat diakses
- Tidak perlu mengatur submenu satu per satu

## Struktur Database

### Tabel: user_web
Kolom baru yang ditambahkan:
```sql
menu_permissions JSON NULL
```

**Database:** `ibnusinadev` (MySQL)

Contoh data:
```json
["dashboard", "pasien", "farmasi"]
```

**Instalasi:**
Kolom sudah ditambahkan ke database dengan perintah:
```sql
ALTER TABLE user_web ADD COLUMN menu_permissions JSON NULL AFTER status;
```

## API Endpoints

### Get All Users (with permissions)
```
GET /api/users
```
Response akan include field `menu_permissions`

### Update User Permissions
```
PUT /api/users/{id}
{
  "menu_permissions": ["dashboard", "farmasi"]
}
```

### Login (returns permissions)
```
POST /api/login
```
Response akan include `menu_permissions` di user object

## Implementasi Frontend

### AppLayout.vue
- Computed property `menuItems` memfilter menu berdasarkan `user.menu_permissions`
- Admin mendapat akses penuh tanpa filter
- User tanpa permissions mendapat akses ke semua menu (backward compatible)

### LoginView.vue
- Fungsi `getFirstAllowedRoute()` menentukan redirect path berdasarkan menu pertama yang diizinkan
- User dengan permission terbatas langsung diarahkan ke menu pertama mereka
- Admin selalu diarahkan ke Dashboard

### Router Guard (router/index.js)
- Fungsi `hasAccessToRoute()` mengecek apakah user punya akses ke route tertentu
- Jika user mencoba akses route yang tidak diizinkan, otomatis redirect ke menu pertama
- Mapping path ke menu key untuk validasi permission

### Modal Kelola Hak Akses
- Grid interaktif dengan card untuk setiap menu
- Checkbox untuk select/deselect
- Visual feedback saat menu dipilih

## Testing

### Test Case 1: Admin Access
- Login sebagai admin
- Verifikasi semua menu terlihat di sidebar

### Test Case 2: User dengan Permissions
- Set user dengan permissions = ["farmasi"]
- Login sebagai user tersebut
- Verifikasi hanya menu Farmasi yang muncul

### Test Case 3: User Tanpa Permissions
- Set user dengan permissions = null
- Login sebagai user tersebut
- Verifikasi semua menu terlihat (backward compatible)

## Troubleshooting

### Menu tidak muncul setelah diatur
- **Solusi**: Logout dan login ulang untuk refresh data user di localStorage

### Perubahan tidak tersimpan
- **Cek**: Pastikan role user bukan "admin"
- **Cek**: Lihat response API di Network tab browser

### User tidak bisa akses halaman setelah pembatasan
- **Solusi**: Ini adalah behavior yang diharapkan
- **Alternatif**: Tambahkan menu yang diperlukan ke hak akses user

