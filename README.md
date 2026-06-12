# 🌶️ REST API Absensi Seblak Prasmanan

REST API berbasis PHP yang bisa di-deploy ke **Vercel**, terhubung ke **Supabase** sebagai database.

---

## 📁 Struktur File

```
absensi-api/
├── vercel.json          ← Konfigurasi Vercel (PHP runtime + routing)
├── index.php            ← Halaman dokumentasi API
└── api/
    ├── config.php       ← Koneksi Supabase + helper functions
    └── v1/
        ├── auth.php         ← POST  /api/v1/auth.php
        ├── users.php        ← CRUD  /api/v1/users.php
        └── attendances.php  ← CRUD  /api/v1/attendances.php
```

---

## 🚀 Cara Deploy ke Vercel

### 1. Push ke GitHub
```bash
git init
git add .
git commit -m "init: REST API absensi"
git remote add origin https://github.com/username/absensi-api.git
git push -u origin main
```

### 2. Deploy di Vercel
- Buka [vercel.com](https://vercel.com) → **Add New Project**
- Import repo GitHub kamu
- **Framework Preset:** Other
- **Root Directory:** `.` (biarkan default)
- Klik **Deploy** ✅

### 3. Ganti Base URL di `index.php`
```php
$base = 'https://NAMA-PROJECT-KAMU.vercel.app';
```

---

## ⚙️ Konfigurasi Supabase

Edit file `api/config.php`:
```php
define('SUPABASE_URL', 'https://xxxx.supabase.co');
define('SUPABASE_KEY', 'eyJhbGci...');
```

### Whitelist Domain di Supabase
Buka **Supabase → Settings → API → Allowed Origins**, tambahkan:
```
https://NAMA-PROJECT-KAMU.vercel.app
```

---

## 📡 Endpoint Lengkap

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/api/v1/auth.php` | Login |
| GET | `/api/v1/users.php` | Semua karyawan |
| GET | `/api/v1/users.php?id=UUID` | Karyawan by ID |
| POST | `/api/v1/users.php` | Tambah karyawan |
| PUT | `/api/v1/users.php?id=UUID` | Edit karyawan |
| DELETE | `/api/v1/users.php?id=UUID` | Hapus karyawan |
| GET | `/api/v1/attendances.php` | Semua absensi |
| GET | `/api/v1/attendances.php?user_id=X` | Absensi by user |
| GET | `/api/v1/attendances.php?tanggal=YYYY-MM-DD` | Absensi by tanggal |
| GET | `/api/v1/attendances.php?check_today=USER_ID` | Cek absen hari ini |
| GET | `/api/v1/attendances.php?rekap=USER_ID` | Rekap per user |
| GET | `/api/v1/attendances.php?rekap_total=1` | Rekap total |
| POST | `/api/v1/attendances.php` | Tambah absensi + upload |
| PUT | `/api/v1/attendances.php?id=UUID` | Edit absensi |
| DELETE | `/api/v1/attendances.php?id=UUID` | Hapus absensi |

---

## 📱 Integrasi Flutter

Ganti `AttendanceService` dan `AuthService` agar hit endpoint ini
sebagai pengganti koneksi Supabase langsung.

Contoh login dari Flutter:
```dart
final res = await http.post(
  Uri.parse('https://KAMU.vercel.app/api/v1/auth.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({'email': email, 'password': password}),
);
```

Upload absensi (multipart):
```dart
final req = http.MultipartRequest(
  'POST',
  Uri.parse('https://KAMU.vercel.app/api/v1/attendances.php'),
);
req.fields['id'] = uuid;
req.fields['user_id'] = userId;
req.fields['nama_karyawan'] = nama;
req.fields['status'] = 'hadir';
req.fields['tanggal'] = '2025-01-01';
req.files.add(await http.MultipartFile.fromPath('foto', fotoPath));
final res = await req.send();
```
