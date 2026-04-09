# TaskFlow — Manajemen Tugas
Aplikasi web PHP + MySQL untuk mengelola tugas/to-do.

## Fitur
- ✅ CRUD lengkap (Tambah, Lihat, Edit, Hapus)
- 🔍 Pencarian real-time & filter (status, prioritas)
- 📊 Dashboard statistik (total, selesai, proses, urgent)
- 🎯 Prioritas tugas (tinggi / sedang / rendah)
- 📅 Deadline dengan indikator overdue
- ⚡ Tandai selesai dengan 1 klik
- 📱 Responsive (mobile-friendly)

## Struktur File
```
app/
├── index.php    ← Halaman utama + UI
├── api.php      ← REST API handler (CRUD)
├── config.php   ← Konfigurasi database
└── setup.sql    ← Script inisialisasi database
```

## Cara Setup

### 1. Persyaratan
- PHP >= 7.4 (dengan ekstensi PDO & PDO_MySQL)
- MySQL >= 5.7 atau MariaDB >= 10.3
- Web server: Apache/Nginx atau `php -S` (dev)

### 2. Setup Database
```bash
# Masuk ke MySQL
mysql -u root -p

# Jalankan script SQL
source /path/to/setup.sql;
# atau
mysql -u root -p < setup.sql
```

### 3. Konfigurasi
Edit `config.php` sesuai kredensial MySQL Anda:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // username MySQL
define('DB_PASS', '');           // password MySQL
define('DB_NAME', 'taskmanager');
```

### 4. Jalankan Aplikasi

**Dengan PHP built-in server (development):**
```bash
cd app/
php -S localhost:8000
# Buka browser: http://localhost:8000
```

**Dengan XAMPP/WAMP/Laragon:**
- Salin folder `app/` ke `htdocs/` atau `www/`
- Buka browser: `http://localhost/app/`

**Dengan Apache/Nginx di server:**
- Upload ke direktori web root
- Pastikan mod_rewrite aktif (Apache)

## API Endpoint

| Method | URL              | Fungsi           |
|--------|------------------|------------------|
| GET    | `api.php`        | Ambil semua tugas|
| GET    | `api.php?id=1`   | Ambil 1 tugas    |
| POST   | `api.php`        | Buat tugas baru  |
| PUT    | `api.php?id=1`   | Update tugas     |
| DELETE | `api.php?id=1`   | Hapus tugas      |

### Query Parameters (GET all):
- `q` — pencarian teks
- `status` — filter: belum / proses / selesai
- `prioritas` — filter: rendah / sedang / tinggi
- `sort` — urut: dibuat_pada / deadline / prioritas / judul
- `dir` — arah: asc / desc

### Body POST/PUT (JSON):
```json
{
  "judul":     "Nama tugas",
  "deskripsi": "Detail opsional",
  "prioritas": "sedang",
  "status":    "belum",
  "deadline":  "2026-04-30"
}
```
