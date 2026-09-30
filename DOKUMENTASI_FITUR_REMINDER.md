# Dokumentasi Fitur: Reminder Tagihan & Pembayaran (Terintegrasi WhatsApp & Laporan Keuangan)

Dokumen ini memuat spesifikasi lengkap, arsitektur database yang telah diselaraskan dengan skema eksisting, alur kerja (*workflow*), serta panduan teknis implementasi modul **Reminder Tagihan**.

---

## 1. Analisis & Penyesuaian Skema Database Eksisting

Pada perancangan awal, modul reminder mereferensikan tabel generic `transactions`. Namun, di aplikasi **Monitoring Keluarga**, sistem pencatatan keuangan yang sudah aktif menggunakan tabel **`expenses`**. 

Agar fitur baru ini **tidak merusak kode lama** dan langsung terhubung dengan Dashboard, Grafik Saldo, serta Chatbot WhatsApp yang sudah ada, penyesuaian berikut diterapkan:

| Komponen di Dokumen Awal | Disesuaikan dengan Sistem Eksisting | Keterangan / Alasan |
| :--- | :--- | :--- |
| Tabel `transactions` | **Tabel `expenses`** | Seluruh rekap laporan keuangan, dashboard, dan bot WA membaca dari tabel `expenses`. Model `Transaction.php` di aplikasi ini juga dialiaskan ke tabel `expenses` (`protected $table = 'expenses';`). |
| Kolom `amount` | **`nominal`** (BigInteger) | Sesuai tipe data integer Rupiah pada `expenses.nominal`. |
| Kolom `type` (`expense`/`income`) | **`jenis_transaksi`** (`keluar`/`masuk`) | Nilai enum yang konsisten dengan filter dashboard dan chatbot. |
| Kolom `description` | **`keterangan`** (String) | Kolom keterangan deskripsi transaksi. |
| Nomor Telepon | **`wa_number`** & **`users.nomor_hp`** | Nomor WhatsApp pengirim/tujuan. |
| Kategori | **`categories.nama_kategori`** | Kategori tagihan diambil langsung dari master kategori. |

---

## 2. Arsitektur Tabel Database Baru

### A. Tabel `reminders`
Menyimpan jadwal dan status tagihan yang perlu diingatkan.

```sql
CREATE TABLE reminders (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    wa_number VARCHAR(20) NOT NULL,
    nama_tagihan VARCHAR(100) NOT NULL,
    jenis_transaksi VARCHAR(10) DEFAULT 'keluar', -- 'keluar' atau 'masuk'
    total_nominal BIGINT NOT NULL,
    sisa_tagihan BIGINT NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    perulangan VARCHAR(20) DEFAULT 'tidak',       -- 'tidak', 'mingguan', 'bulanan', 'tahunan'
    keterangan TEXT NULL,
    status VARCHAR(20) DEFAULT 'aktif',           -- 'aktif', 'lunas', 'overdue', 'nonaktif'
    last_notified_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### B. Tabel `reminder_payment_histories`
Menyimpan riwayat setiap transaksi pembayaran (pelunasan maupun cicilan bertahap). Tabel ini memiliki relasi langsung ke tabel `expenses`.

```sql
CREATE TABLE reminder_payment_histories (
    id BIGSERIAL PRIMARY KEY,
    reminder_id BIGINT NOT NULL REFERENCES reminders(id) ON DELETE CASCADE,
    expense_id BIGINT NULL REFERENCES expenses(id) ON DELETE SET NULL,
    nominal_bayar BIGINT NOT NULL,
    tanggal_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sumber VARCHAR(20) DEFAULT 'web',             -- 'web' atau 'whatsapp'
    catatan VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## 3. Alur Kerja (Workflow) Sistem

### A. Alur Simultan Saat Pembayaran / Cicilan
Setiap kali pengguna melakukan pembayaran (baik dari Dashboard Web maupun balasan WhatsApp), sistem menjalankan 3 proses database secara atomik (`DB::transaction`):

```text
[Aksi Bayar / Cicil Tagihan]
        │
        ├──> 1. UPDATE table `reminders`
        │       ├── sisa_tagihan = sisa_tagihan - nominal_bayar
        │       ├── tanggal_jatuh_tempo = tanggal_baru (khusus tagihan 'tidak' berulang jika ada perpanjangan cicil; tagihan berulang tetap pada periode berjalan hingga lunas)
        │       └── status = (sisa_tagihan <= 0) ? 'lunas' : 'aktif'
        │
        ├──> 2. INSERT table `expenses` (Laporan Keuangan Eksisting)
        │       ├── wa_number       = reminder.wa_number
        │       ├── keterangan      = "Pembayaran: " + reminder.nama_tagihan + (" (Lunas)" / " (Cicilan)")
        │       ├── kategori        = reminder.kategori
        │       ├── nominal         = nominal_bayar
        │       ├── jenis_transaksi = reminder.jenis_transaksi
        │       └── recorded_at     = NOW()
        │
        ├──> 3. INSERT table `reminder_payment_histories` (Log Audit)
        │       ├── reminder_id     = reminder.id
        │       ├── expense_id      = expense.id (Terhubung langsung ke record laporan keuangan)
        │       ├── nominal_bayar   = nominal_bayar
        │       ├── tanggal_bayar   = NOW()
        │       └── sumber          = 'whatsapp' / 'web'
        │
        └──> 4. AUTO-INSERT Tagihan Periode Berikutnya (Jika Tagihan Lunas & Berulang)
                ├── Kondisi         : sisa_tagihan <= 0 DAN perulangan != 'tidak'
                ├── Jatuh Tempo Baru: +1 bulan (bulanan), +1 tahun (tahunan), +1 minggu (mingguan)
                ├── Sisa Tagihan    : Direset kembali ke total_nominal awal
                └── Status          : 'aktif'
```

> **Dampak Positif:** Saldo dashboard, grafik keuangan bulanan, dan rekap WhatsApp Bot langsung ter-update secara otomatis tanpa perubahan pada modul laporan keuangan lama. Selain itu, tagihan berulang (bulanan/tahunan) otomatis ter-insert untuk periode berikutnya begitu periode saat ini lunas.

---

### B. Notifikasi Proaktif Otomatis (Scheduler)
* **Waktu Pengiriman:** Dijalankan via scheduler harian Laravel:
  ```bash
  php artisan reminder:send
  ```
  *(Sudah dijadwalkan otomatis setiap hari pukul 08:00 WIB di `routes/console.php`).*
* **Kriteria H-3 hingga Hari H:**
  Sistem mengecek tagihan yang berstatus `aktif` dengan `tanggal_jatuh_tempo` dalam rentang H-3 sampai H-0. Pesan WA dikirimkan berisi detail nominal, tanggal jatuh tempo, dan instruksi pelunasan/cicilan.
* **Kriteria Overdue (Telat):**
  Jika sudah melewati jatuh tempo, status tagihan otomatis menjadi `overdue`. Pesan peringatan dikirimkan pada **H+1**, **H+3**, atau mingguan hingga tagihan diselesaikan.
* **Pencegahan Spam:** Kolom `last_notified_at` memastikan setiap tagihan maksimal hanya mengirim 1 notifikasi per hari.

---

### C. Two-Way Messaging via WhatsApp Bot

Pengguna dapat merespons notifikasi WhatsApp secara langsung dengan beberapa format:

#### 1. Cek Daftar Tagihan
* **Ketik:** `tagihan` atau `reminder`
* **Respon Bot:** Menampilkan seluruh tagihan aktif, sisa nominal, dan tanggal jatuh tempo beserta ID masing-masing tagihan.

#### 2. Melunasi Tagihan
* **Format:** `SELESAI [ID]` atau `LUNAS [ID]`
* **Contoh:** `SELESAI 5` *(atau cukup `SELESAI` jika hanya ada 1 tagihan aktif)*.
* **Respon Bot:** Mengonfirmasi pelunasan, mencatat pengeluaran di laporan keuangan, dan menampilkan saldo terkini.

#### 3. Membayar Dicicil / Parsial
* **Format:** `CICIL [Nominal] [Tanggal_Jatuh_Tempo_Baru]` atau `CICIL [ID] [Nominal] [Tanggal_Jatuh_Tempo_Baru]`
* **Contoh:** `CICIL 400000 25/10/2026` atau `CICIL 5 400000 25/10/2026`
* **Respon Bot:** Mencatat pengeluaran Rp 400.000, mengupdate sisa tagihan, menetapkan jatuh tempo baru, dan mencatat riwayat cicilan.

---

### D. Fitur Dashboard Web

1. **Ringkasan Kartu Statistik:**
   - Total Sisa Tagihan Aktif (Rp)
   - Jumlah Tagihan Segera Jatuh Tempo (H-7)
   - Jumlah Tagihan Overdue / Telat Bayar (Visual Merah)
2. **Filter & Pencarian:**
   - Filter Status: *Aktif & Telat*, *Sudah Lunas*, *Khusus Overdue*, *Nonaktif/Jeda*, *Semua Status*.
   - Pencarian berdasarkan nama tagihan, kategori, atau nomor WhatsApp.
3. **Aksi Pembayaran:**
   - Tombol `[Bayar]`: Membuka modal popup. Tersedia opsi *Bayar Lunas Semua* atau input nominal cicilan parsial beserta tanggal jatuh tempo baru untuk sisa tagihan. Menampilkan pula tabel riwayat cicilan sebelumnya.
4. **Edit & Manajemen:**
   - Tombol `[Edit]`: Memperbarui data tagihan atau tanggal jatuh tempo tanpa menghapus riwayat pembayaran lama.
   - Tombol `[Jeda / Aktifkan]`: Menonaktifkan sementara reminder agar trigger WA berhenti.
   - Tombol `[Hapus]`: Menghapus jadwal tagihan.

---

## 4. Daftar File yang Dibuat & Dimodifikasi

### File Baru:
1. `database/migrations/2026_09_24_000001_create_reminders_table.php` — Migration tabel reminders.
2. `database/migrations/2026_09_24_000002_create_reminder_payment_histories_table.php` — Migration riwayat pembayaran.
3. `app/Models/Reminder.php` — Model Reminder dengan relasi ke User, Payments, dan scope jatuh tempo/overdue.
4. `app/Models/ReminderPaymentHistory.php` — Model riwayat pembayaran dengan relasi ke Reminder dan Expense.
5. `app/Actions/ReminderAction.php` — Logika bisnis penyimpanan tagihan, pembayaran cicilan/lunas, dan integrasi ke tabel `expenses`.
6. `app/Console/Commands/SendReminderNotification.php` — Command artisan `reminder:send` untuk pengiriman WA otomatis H-3 s.d H-1 dan Overdue.
7. `app/Http/Controllers/ReminderController.php` — Controller CRUD web dan modal dialog Bootbox.
8. `resources/views/reminder/index.blade.php` — Halaman tabel reminder, filter, dan kartu statistik.
9. `resources/views/reminder/bbox_reminder.blade.php` — Modal form tambah / edit tagihan.
10. `resources/views/reminder/bbox_bayar.blade.php` — Modal form bayar lunas / cicilan tagihan.
11. `public/assets/js/reminder.js` — AJAX handler, format angka ribuan, dan interaksi Bootbox modal.

### File yang Dimodifikasi (Tetap Aman & Kompatibel):
1. `routes/web.php` — Penambahan rute grup `/reminder` (terproteksi middleware auth).
2. `routes/console.php` — Pendaftaran jadwal otomatis harian `reminder:send`.
3. `resources/views/layouts/app.blade.php` — Penambahan link menu navigasi *Reminder Tagihan* di sidebar.
4. `app/Services/MessageParser.php` — Penambahan parser perintah `tagihan`, `selesai`, `lunas`, dan `cicil` tanpa mengubah parser transaksi lama.
5. `app/Jobs/ProcessWhatsAppMessage.php` — Penanganan respon WhatsApp bot untuk tagihan, pelunasan, dan cicilan.

---

## 5. Panduan Menjalankan di Lingkungan Lokal / Server

1. **Jalankan Migration Database:**
   ```bash
   php artisan migrate
   ```
2. **Jalankan Uji Coba Pengingat WhatsApp Manual:**
   ```bash
   php artisan reminder:send
   ```
3. **Akses Dashboard Web:**
   Buka browser pada menu navigasi:
   `http://localhost:8000/reminder`
