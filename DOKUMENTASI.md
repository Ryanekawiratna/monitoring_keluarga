# WA Keuangan Bot
## Laravel 12 + KirimDev + Supabase

---

## Struktur Project

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── WhatsAppController.php    ← Webhook KirimDev
│   │   ├── DashboardController.php   ← Statistik & data
│   │   └── AuthController.php        ← Login dashboard
│   └── Middleware/
│       └── DashboardAuth.php         ← Proteksi halaman dashboard
├── Jobs/
│   └── ProcessWhatsAppMessage.php    ← Logika chatbot (async)
├── Models/
│   └── Expense.php
└── Services/
    ├── KirimDevService.php           ← Kirim pesan via KirimDev
    ├── MessageParser.php             ← Parse format pesan
    └── WebhookVerifier.php           ← Verifikasi HMAC-SHA256

resources/views/
├── layouts/app.blade.php             ← Layout sidebar
├── auth/login.blade.php              ← Halaman login
└── dashboard/
    ├── index.blade.php               ← Dashboard utama
    └── transaksi.blade.php           ← Tabel transaksi

config/services.php
routes/{web.php,api.php}
database/migrations/
```

---

## STEP 1 — Buat Project Laravel 12

```bash
composer create-project laravel/laravel wa-keuangan
cd wa-keuangan
```

---

## STEP 2 — Salin Semua File

Salin seluruh file dari package ini ke project Laravel sesuai struktur di atas.

---

## STEP 3 — Daftarkan Middleware

Buka `bootstrap/app.php`, tambahkan alias middleware:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'dashboard.auth' => \App\Http\Middleware\DashboardAuth::class,
    ]);
})
```

---

## STEP 4 — Setup Supabase sebagai Database

1. Buka **supabase.com** → buat project baru
2. Setelah project siap, buka **Settings → Database**
3. Di bagian **"Connection string"**, pilih mode **"Transaction"** (port 6543)
4. Copy connection string — ambil:
   - Host: `db.xxxxxxxxxxxx.supabase.co`
   - Port: `6543`
   - Database: `postgres`
   - User: `postgres`
   - Password: password yang diset saat buat project

Isi `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
DB_SSLMODE=require
```

---

## STEP 5 — Setup KirimDev

1. Login ke **app.kirimdev.com**
2. **API Key:**
   - Settings → API Keys → buat key baru
   - Copy `kdv_live_...` → isi `KIRIMDEV_API_KEY`
3. **Phone Number ID:**
   - Pilih nomor WA yang terhubung di dashboard
   - Copy Phone Number ID → isi `KIRIMDEV_PHONE_NUMBER_ID`
4. **Webhook Secret:**
   - Webhooks → buat webhook subscription
   - URL: `https://yourdomain.com/api/webhook/whatsapp`
   - Events: centang `message.received`
   - Copy Signing Secret → isi `KIRIMDEV_WEBHOOK_SECRET`

---

## STEP 6 — Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` lengkap sesuai data dari Supabase, KirimDev, dan password dashboard.

---

## STEP 7 — Jalankan Migration & Queue Table

```bash
# Install driver PostgreSQL jika belum ada
composer require doctrine/dbal

# Migration
php artisan migrate

# Tabel queue
php artisan queue:table
php artisan migrate
```

---

## STEP 8 — Testing Lokal (Opsional)

Jika testing di lokal sebelum deploy ke server:

```bash
# Terminal 1
php artisan serve

# Terminal 2
php artisan queue:work

# Terminal 3 — buat webhook bisa diakses dari luar
ngrok http 8000
# Ganti webhook URL di KirimDev dengan URL ngrok yang diberikan
```

---

## STEP 9 — Deploy ke Server Production

```bash
# Set semua .env values production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan queue worker dengan Supervisor
```

Konfigurasi Supervisor (`/etc/supervisor/conf.d/wa-keuangan.conf`):
```ini
[program:wa-keuangan-worker]
command=php /var/www/wa-keuangan/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/wa-keuangan
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/wa-keuangan.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start wa-keuangan-worker
```

---

## Cara Login Dashboard

Buka: `https://yourdomain.com/login`

- Username: nilai `DASHBOARD_USERNAME` di .env (default: `admin`)
- Password: nilai `DASHBOARD_PASSWORD` di .env

---

## Cara Pakai Bot (via WhatsApp)

### Catat pengeluaran
```
makan food 50000
bensin transport 80000
listrik bills 250000
```

### Command
```
rekap          → hari ini
rekap minggu   → minggu ini
rekap bulan    → bulan ini
hapus          → hapus transaksi terakhir
bantuan        → panduan lengkap
```

---

## Troubleshooting

| Masalah | Penyebab | Solusi |
|---|---|---|
| 401 dari webhook | Signature tidak valid | Cek KIRIMDEV_WEBHOOK_SECRET |
| Bot tidak balas | Queue worker mati | `php artisan queue:work` |
| DB connection error | Supabase SSL | Pastikan `DB_SSLMODE=require` |
| View error | Cache lama | `php artisan view:clear` |

Cek log: `tail -f storage/logs/laravel.log`
