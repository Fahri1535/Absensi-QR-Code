# 🔧 Backend Presensi QR — PT. Nugraha Tirta Sejati
### Laravel 12 · Full Backend sesuai Frontend

---

## 📁 Struktur File Backend

```
app/
├── Models/
│   ├── User.php
│   ├── Karyawan.php
│   ├── JadwalKerja.php
│   ├── QrCode.php
│   ├── Presensi.php
│   ├── Izin.php
│   └── Notifikasi.php
│
├── Http/
│   ├── Middleware/
│   │   └── CheckRole.php
│   └── Controllers/
│       ├── Auth/LoginController.php
│       ├── Karyawan/
│       │   ├── DashboardController.php
│       │   ├── PresensiController.php   ← inti scan QR
│       │   ├── RiwayatController.php
│       │   ├── IzinController.php
│       │   └── ProfilController.php
│       ├── Operator/
│       │   ├── DashboardController.php
│       │   ├── KaryawanController.php   ← CRUD karyawan
│       │   ├── QrcodeController.php
│       │   ├── JadwalController.php
│       │   └── LaporanController.php
│       ├── Hrd/
│       │   ├── DashboardController.php
│       │   ├── IzinController.php       ← approve/tolak
│       │   └── LaporanController.php
│       └── NotifikasiController.php
│
├── Exports/
│   ├── RiwayatPresensiExport.php
│   └── LaporanPresensiExport.php
│
└── Providers/AppServiceProvider.php

database/
├── migrations/                          ← 7 migrasi lengkap
└── seeders/DatabaseSeeder.php

routes/web.php                           ← semua route
bootstrap/app.php                        ← register middleware
config/auth.php                          ← login pakai username
```

---

## 🚀 Langkah Instalasi

### 1. Buat project Laravel 12

```bash
composer create-project laravel/laravel absensi_qr
cd absensi_qr
```

### 2. Install dependencies

```bash
composer require simplesoftwareio/simple-qrcode
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

### 3. Salin file backend ini ke project

Salin semua file dari folder ini ke struktur yang sama di project Laravel.

### 4. Konfigurasi `.env`

```env
APP_NAME="Presensi QR"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_qr
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

### 5. Setup database

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 6. Salin frontend (blade + assets)

```bash
# Dari frontend_output/
cp app.css   public/css/app.css
cp app.js    public/js/app.js
cp -r layouts/   resources/views/layouts/
cp -r auth/      resources/views/auth/
cp -r karyawan/  resources/views/karyawan/
cp -r operator/  resources/views/operator/
cp -r hrd/       resources/views/hrd/
cp -r shared/    resources/views/shared/
cp notifikasi.blade.php resources/views/
```

### 7. Jalankan

```bash
php artisan serve
```

---

## 🔑 Akun Default (setelah seeder)

| Role     | Username   | Password      |
|----------|------------|---------------|
| Operator | operator   | operator123   |
| HRD      | hrd        | hrd123        |
| Karyawan | budi       | karyawan123   |
| Karyawan | siti       | karyawan123   |
| Karyawan | ahmad      | karyawan123   |

---

## 🗄️ Skema Database

### `users`
| Kolom      | Tipe                           |
|------------|--------------------------------|
| id         | bigint PK                      |
| username   | varchar(50) UNIQUE             |
| password   | varchar hashed                 |
| role       | enum: karyawan, operator, hrd  |

### `karyawan`
| Kolom          | Keterangan                    |
|----------------|-------------------------------|
| user_id        | FK → users                    |
| nama_lengkap   | —                             |
| jabatan        | nullable                      |
| nomor_telepon  | nullable                      |
| foto           | path storage, nullable        |
| status         | aktif / nonaktif              |
| kode_karyawan  | unik, untuk QR personal       |

### `jadwal_kerja`
Satu baris (singleton), menyimpan jam_masuk, jam_pulang, toleransi_menit, hari_kerja.

### `qr_codes`
| tipe   | kode_qr (random string panjang) | is_active |
QR permanen yang ditempel di kantor — masuk & pulang.

### `presensi`
| karyawan_id | tanggal | jam_datang | jam_pulang | status_masuk | status_pulang |
UNIQUE (karyawan_id, tanggal).

### `izin`
Menyimpan pengajuan izin/cuti/sakit. Status: pending → disetujui/ditolak.

### `notifikasi`
Notifikasi in-app per user. Auto-dikirim saat presensi & saat izin diproses.

---

## ⚙️ Logika Bisnis Penting

### Scan QR (PresensiController@scan)
1. Validasi `qr_data` cocok dengan `qr_codes.kode_qr` dan `is_active = true`
2. Cek apakah sudah presensi hari ini
3. Validasi window waktu berdasarkan `jadwal_kerja`
   - Masuk: 15 menit sebelum s/d jam_masuk + toleransi + 60 menit
   - Pulang: 30 menit sebelum jam_pulang s/d jam_pulang + 60 menit
4. Simpan jam dan hitung status (tepat_waktu / terlambat)
5. Return JSON → ditangkap JS frontend

### Notifikasi (otomatis)
- Presensi masuk/pulang → notif ke karyawan bersangkutan
- Izin baru → notif ke semua user HRD
- Izin diproses HRD → notif ke karyawan

### View Composer (AppServiceProvider)
`$notifUnread` dan `$notifCount` tersedia di semua view secara otomatis.
