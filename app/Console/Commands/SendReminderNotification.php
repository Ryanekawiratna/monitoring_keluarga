<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use App\Services\KirimDevService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendReminderNotification extends Command
{
  protected $signature = 'reminder:send';
  protected $description = 'Kirim notifikasi WhatsApp otomatis untuk tagihan jatuh tempo (H-3 s.d H-1) dan Overdue (H+1/H+3)';

  public function handle(KirimDevService $kirimdev): int
  {
    $today = Carbon::today();
    $this->info("Menjalankan pengecekan reminder tagihan untuk tanggal: {$today->toDateString()}");

    // Ambil semua tagihan yang aktif atau overdue (bukan lunas dan bukan nonaktif)
    $reminders = Reminder::whereIn('status', ['aktif', 'overdue'])
      ->where('sisa_tagihan', '>', 0)
      ->get();

    $sentCount = 0;

    foreach ($reminders as $reminder) {
      $dueDate = Carbon::parse($reminder->tanggal_jatuh_tempo)->startOfDay();
      $diffDays = $today->diffInDays($dueDate, false); // Positif: belum jatuh tempo (H-), Negatif: lewat (H+)

      // Cek apakah sudah dikirim notifikasi hari ini
      if ($reminder->last_notified_at && Carbon::parse($reminder->last_notified_at)->isToday()) {
        continue;
      }

      $message = null;

      // 1. Tagihan H-3 sampai H-1 atau Hari H (H-0)
      if ($diffDays >= 0 && $diffDays <= 3) {
        $labelHari = $diffDays == 0 ? "HARI INI" : "H-{$diffDays}";
        $message = implode("\n", [
          "🔔 *PENGINGAT TAGIHAN ({$labelHari})*",
          "━━━━━━━━━━━━━━",
          "Halo, berikut adalah pengingat tagihan Anda:",
          "",
          "📌 *Tagihan:* {$reminder->nama_tagihan}",
          "💰 *Sisa Tagihan:* Rp " . number_format($reminder->sisa_tagihan, 0, ',', '.'),
          "📅 *Jatuh Tempo:* {$dueDate->locale('id')->translatedFormat('d F Y')}",
          "🏷️ *Kategori:* {$reminder->kategori}",
          $reminder->keterangan ? "📝 *Catatan:* {$reminder->keterangan}" : "",
          "",
          "━━━━━━━━━━━━━━",
          "💡 *Aksi Langsung via WhatsApp:*",
          "• Ketik *SELESAI {$reminder->id}* (bila sudah lunas)",
          "• Ketik *CICIL {$reminder->id} [Nominal] [Tgl_Jatuh_Tempo_Baru]*",
          "  _Contoh: CICIL {$reminder->id} 400000 " . $today->copy()->addMonth()->format('d/m/Y') . "_",
        ]);
      }
      // 2. Tagihan Overdue / Telat (H+1 atau H+3)
      elseif ($diffDays < 0) {
        $hariTelat = abs($diffDays);
        if ($reminder->status !== 'overdue') {
          $reminder->update(['status' => 'overdue']);
        }

        // Kirim follow up pada H+1 atau H+3
        if ($hariTelat == 1 || $hariTelat == 3 || $hariTelat % 7 == 0) {
          $message = implode("\n", [
            "⚠️ *TAGIHAN TELAT DIBAYAR (OVERDUE)*",
            "━━━━━━━━━━━━━━",
            "Perhatian! Tagihan Anda telah melewati jatuh tempo ({$hariTelat} hari lalu):",
            "",
            "📌 *Tagihan:* {$reminder->nama_tagihan}",
            "💰 *Sisa Tagihan:* Rp " . number_format($reminder->sisa_tagihan, 0, ',', '.'),
            "📅 *Jatuh Tempo:* {$dueDate->locale('id')->translatedFormat('d F Y')}",
            "",
            "━━━━━━━━━━━━━━",
            "Mohon segera lakukan penyelesaian:",
            "• Ketik *SELESAI {$reminder->id}*",
            "• Ketik *CICIL {$reminder->id} [Nominal] [Tgl_Jatuh_Tempo_Baru]*",
          ]);
        }
      }

      if ($message && !empty($reminder->wa_number)) {
        $success = $kirimdev->sendText($reminder->wa_number, $message);
        if ($success) {
          $reminder->update(['last_notified_at' => now()]);
          $sentCount++;
          $this->info("Notifikasi terkirim ke {$reminder->wa_number} untuk {$reminder->nama_tagihan}");
        }
      }
    }

    $this->info("Pengecekan selesai. {$sentCount} notifikasi terkirim.");
    return Command::SUCCESS;
  }
}
