<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\User;
use App\Services\KirimDevService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendExpenseReminderNotification extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'expense:reminder-send 
                          {--session= : Sesi reminder (siang atau sore)} 
                          {--to= : Nomor WhatsApp tujuan untuk uji coba} 
                          {--dry-run : Uji coba tanpa mengirim pesan WhatsApp sebenarnya}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Kirim pengingat WhatsApp harian untuk pencatatan keuangan keluarga (jam 10 siang dan jam 7 sore)';

  public function handle(KirimDevService $kirimdev): int
  {
    $now = Carbon::now('Asia/Jakarta');
    $session = strtolower((string) $this->option('session'));

    // Tentukan sesi otomatis jika tidak ditentukan via opsi
    if (!in_array($session, ['siang', 'sore'])) {
      $session = ($now->hour < 15) ? 'siang' : 'sore';
    }

    $isDryRun = (bool) $this->option('dry-run');
    $customTo = $this->option('to');

    $this->info("=== Pengingat Pencatatan Keuangan (Sesi: " . strtoupper($session) . ") ===");
    $this->info("Waktu: {$now->format('Y-m-d H:i:s')} WIB");

    if ($isDryRun) {
      $this->warn("[DRY RUN MODE] Pesan tidak akan dikirim ke WhatsApp API.");
    }

    // 1. Kumpulkan daftar penerima (Nomor HP & Nama)
    $recipients = collect();

    if (!empty($customTo)) {
      $cleanNumber = $this->formatPhoneNumber($customTo);
      $user = User::where('nomor_hp', $cleanNumber)->first();
      $recipients->put($cleanNumber, $user?->nama);
    } else {
      // Ambil seluruh user terdaftar yang memiliki nomor HP
      User::query()
        ->whereNotNull('nomor_hp')
        ->where('nomor_hp', '!=', '')
        ->get(['nama', 'nomor_hp'])
        ->each(function ($user) use ($recipients) {
          $num = $this->formatPhoneNumber($user->nomor_hp);
          if ($num) {
            $recipients->put($num, $user->nama);
          }
        });

      // Tambahkan nomor dari riwayat transaksi yang belum ada di tabel users
      Expense::query()
        ->whereNotNull('wa_number')
        ->where('wa_number', '!=', '')
        ->distinct()
        ->pluck('wa_number')
        ->each(function ($waNumber) use ($recipients) {
          $num = $this->formatPhoneNumber($waNumber);
          if ($num && !$recipients->has($num)) {
            $recipients->put($num, null);
          }
        });
    }

    if ($recipients->isEmpty()) {
      $this->warn("Tidak ada nomor penerima yang ditemukan.");
      return Command::SUCCESS;
    }

    $this->info("Jumlah penerima: " . $recipients->count());
    $sentCount = 0;

    foreach ($recipients as $number => $nama) {
      $message = $this->buildMessage($number, $nama, $session, $now);

      $this->line("--------------------------------------------------");
      $this->line("Kirim ke: {$number} (" . ($nama ?? 'Tanpa Nama') . ")");

      if ($isDryRun) {
        $this->line($message);
        $sentCount++;
        continue;
      }

      $success = $kirimdev->sendText($number, $message);
      if ($success) {
        $sentCount++;
        $this->info("✓ Berhasil terkirim ke {$number}");
      } else {
        $this->error("✗ Gagal mengirim ke {$number}");
      }
    }

    $this->line("--------------------------------------------------");
    $this->info("Selesai. {$sentCount} pengingat berhasil diproses.");

    return Command::SUCCESS;
  }

  /**
   * Susun template pesan reminder berdasarkan sesi dan status transaksi hari ini
   */
  private function buildMessage(string $number, ?string $nama, string $session, Carbon $now): string
  {
    $tglStr = $now->locale('id')->translatedFormat('l, d F Y');
    $salamNama = $nama ? ", *" . trim($nama) . "*" : "";

    // Ambil data transaksi hari ini untuk nomor ini
    $todayExpenses = Expense::where('wa_number', $number)
      ->whereDate('recorded_at', $now->toDateString())
      ->get();

    $count = $todayExpenses->count();
    $totalKeluar = $todayExpenses->where('jenis_transaksi', 'keluar')->sum('nominal');
    $totalMasuk  = $todayExpenses->where('jenis_transaksi', 'masuk')->sum('nominal');
    $saldo       = Expense::hitungSaldo($number);

    // Bagian ringkasan status hari ini
    if ($count > 0) {
      $statusLines = [
        "📊 *Status Hari Ini ({$tglStr}):*",
        "• Transaksi: {$count} catatan",
      ];
      if ($totalMasuk > 0) {
        $statusLines[] = "• Total Masuk: Rp " . number_format($totalMasuk, 0, ',', '.');
      }
      $statusLines[] = "• Total Keluar: Rp " . number_format($totalKeluar, 0, ',', '.');
      $statusLines[] = "• Total Saldo: *Rp " . number_format($saldo, 0, ',', '.') . "*";
    } else {
      $statusLines = [
        "📊 *Status Hari Ini ({$tglStr}):*",
        "• _Belum ada transaksi yang dicatat hari ini._",
      ];
    }
    $statusText = implode("\n", $statusLines);

    if ($session === 'siang') {
      return implode("\n", [
        "☀️ *PENGINGAT PENCATATAN KEUANGAN*",
        "━━━━━━━━━━━━━━━━━━━━",
        "Halo{$salamNama}! 👋",
        "Jangan lupa catat pemasukan dan pengeluaran Anda untuk pagi/siang ini ya.",
        "",
        $statusText,
        "",
        "━━━━━━━━━━━━━━━━━━━━",
        "💡 *Format Cepat via WhatsApp:*",
        "• Ketik: *keterangan kategori nominal*",
        "  _Contoh: makan siang food 25000_",
        "  _Contoh: bensin transport 35000_",
        "• Ketik *rekap* untuk mengecek rekap hari ini",
        "• Ketik *bantuan* untuk melihat petunjuk lengkap",
        "",
        "Luangkan waktu sejenak agar keuangan tetap rapi dan terkontrol! ✨",
      ]);
    }

    // Sesi Sore / Malam
    return implode("\n", [
      "🌙 *PENGINGAT PENCATATAN KEUANGAN*",
      "━━━━━━━━━━━━━━━━━━━━",
      "Halo{$salamNama}! 👋",
      "Sudah selesai beraktivitas hari ini? Yuk luangkan 1 menit untuk mencatat seluruh pengeluaran & pemasukan hari ini sebelum istirahat.",
      "",
      $statusText,
      "",
      "━━━━━━━━━━━━━━━━━━━━",
      "💡 *Format Cepat via WhatsApp:*",
      "• Ketik: *keterangan kategori nominal*",
      "  _Contoh: makan malam food 30000_",
      "  _Contoh: jajan martabak snack 20000_",
      "• Ketik *rekap* untuk melihat seluruh transaksi hari ini",
      "• Ketik *bantuan* untuk melihat petunjuk lengkap",
      "",
      "Mencatat rutin bikin keuangan keluarga makin sehat & terencana! 💪",
    ]);
  }

  /**
   * Bersihkan nomor HP agar formatnya 628xxx
   */
  private function formatPhoneNumber(string $number): string
  {
    $clean = preg_replace('/[^0-9]/', '', $number);

    if (str_starts_with($clean, '08')) {
      $clean = '628' . substr($clean, 2);
    }

    return $clean;
  }
}
