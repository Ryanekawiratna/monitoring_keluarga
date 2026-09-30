<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Models\Reminder;
use App\Actions\TransactionAction;
use App\Actions\ReminderAction;
use App\Services\GeminiService;
use App\Services\KirimDevService;
use App\Services\MessageParser;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessage implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public int $tries   = 3;
  public int $backoff = 5;

  public function __construct(
    private readonly string $from,
    private readonly string $text,
    private readonly string $messageId,

  ) {}

  public function handle(
    MessageParser  $parser,
    KirimDevService $kirimdev,
    GeminiService  $gemini,
  ): void {
    Log::info('Processing WA message', ['from' => $this->from, 'text' => $this->text]);

    // ── 1. Coba parse dengan regex ────────────────────────────────────
    $parsed = $parser->parse($this->text);



    // ── 2. Fallback ke Gemini jika regex tidak mengenali ─────────────
    if ($parsed['type'] === 'unknown' && config('services.gemini.api_key')) {
      Log::info('Regex parse failed, falling back to Gemini', ['text' => $this->text]);
      $parsed = $gemini->parseMessage($this->text);
    }

    // ── 3. Route ke handler ───────────────────────────────────────────
    $reply = match ($parsed['type']) {
      'transaction' => $this->handleTransaction($parsed['data']),
      'command'     => $this->handleCommand($parsed['intent'], $parsed['data'] ?? []),
      default       => $this->replyUnknown(),
    };

    $kirimdev->sendText($this->from, $reply);
  }

  // ─── Catat Transaksi ────────────────────────────────────────

  private function handleTransaction(array $data): string
  {
    $jenisTransaksi = ($data['jenis_transaksi'] ?? '') === 'masuk' ? 'masuk' : 'keluar';

    Expense::create([
      'wa_number'       => $this->from,
      'keterangan'      => $data['keterangan'],
      'kategori'        => $data['kategori'],
      'nominal'         => $data['nominal'],
      'jenis_transaksi' => $jenisTransaksi,
      'recorded_at'     => now(),
    ]);

    if ($jenisTransaksi === 'masuk') {
      $totalHariIni = Expense::where('wa_number', $this->from)->where('jenis_transaksi', 'masuk')->today()->sum('nominal');
      $labelTotal   = 'Total pemasukan hari ini';
      $labelJenis   = 'Pemasukan';
    } else {
      $totalHariIni = Expense::where('wa_number', $this->from)->where('jenis_transaksi', 'keluar')->today()->sum('nominal');
      $labelTotal   = 'Total pengeluaran hari ini';
      $labelJenis   = 'Pengeluaran';
    }

    $saldo = Expense::hitungSaldo($this->from);

    return implode("\n", [
      "✅ *Tercatat!*",
      "",
      "📝 {$data['keterangan']} ({$data['kategori']})",
      "💰 Rp " . $this->rupiah($data['nominal']),
      "🏷️ Jenis: *{$labelJenis}*",
      "",
      "📊 {$labelTotal}: *Rp " . $this->rupiah((int) $totalHariIni) . "*",
      "💳 Total Saldo: *Rp " . $this->rupiah((int) $saldo) . "*",
    ]);
  }

  // ─── Command Router ─────────────────────────────────────────

  private function handleCommand(string $intent, array $data = []): string
  {
    return match ($intent) {
      'rekap_harian'    => $this->replyRekapHarian(),
      'rekap_mingguan'  => $this->replyRekapMingguan(),
      'rekap_bulanan'   => $this->replyRekapBulanan(),
      'hapus_terakhir'  => $this->replyHapusTerakhir(),
      'daftar_tagihan'  => $this->replyDaftarTagihan(),
      'reminder_lunas'  => $this->replyReminderLunas($data),
      'reminder_cicil'  => $this->replyReminderCicil($data),
      'bantuan'         => $this->replyBantuan(),
      default           => $this->replyUnknown(),
    };
  }

  // ─── Rekap Harian ───────────────────────────────────────────

  private function replyRekapHarian(): string
  {
    $transaksi = Expense::where('wa_number', $this->from)
      ->today()
      ->orderBy('recorded_at')
      ->get();

    if ($transaksi->isEmpty()) {
      return "📭 Belum ada transaksi hari ini.\n\nMulai catat:\n*keterangan kategori nominal*";
    }

    $lines = ["📋 *Rekap Hari Ini*", "━━━━━━━━━━━━━━", ""];
    $totalKeluar = 0;
    $totalMasuk  = 0;

    foreach ($transaksi as $t) {
      $jam     = $t->recorded_at->format('H:i');
      $tanda   = $t->jenis_transaksi === 'masuk' ? '(+)' : '(-)';
      $lines[] = "{$jam} {$tanda} {$t->keterangan} ({$t->kategori})";
      $lines[] = "       Rp " . $this->rupiah($t->nominal);
      if ($t->jenis_transaksi === 'masuk') {
        $totalMasuk += $t->nominal;
      } else {
        $totalKeluar += $t->nominal;
      }
    }

    $saldo = Expense::hitungSaldo($this->from);

    $lines[] = "";
    $lines[] = "━━━━━━━━━━━━━━";
    if ($totalMasuk > 0) {
      $lines[] = "📈 Total Masuk : Rp " . $this->rupiah($totalMasuk);
    }
    $lines[] = "📉 Total Keluar: Rp " . $this->rupiah($totalKeluar);
    $lines[] = "💳 Total Saldo : *Rp " . $this->rupiah((int) $saldo) . "*";

    return implode("\n", $lines);
  }

  // ─── Rekap Mingguan ─────────────────────────────────────────

  private function replyRekapMingguan(): string
  {
    $data = Expense::byNumber($this->from)
      ->thisWeek()
      ->selectRaw('kategori, SUM(nominal) as total, COUNT(*) as jumlah')
      ->groupBy('kategori')
      ->orderByDesc('total')
      ->get();

    if ($data->isEmpty()) {
      return "📭 Belum ada transaksi minggu ini.";
    }

    $mulai  = now()->startOfWeek()->format('d M');
    $akhir  = now()->endOfWeek()->format('d M Y');
    $lines  = ["📊 *Rekap Minggu Ini*", "({$mulai} – {$akhir})", "━━━━━━━━━━━━━━", ""];
    $total  = 0;

    foreach ($data as $item) {
      $lines[] = "• {$item->kategori} ({$item->jumlah}x)";
      $lines[] = "  Rp " . $this->rupiah($item->total);
      $total  += $item->total;
    }

    $lines[] = "";
    $lines[] = "━━━━━━━━━━━━━━";
    $lines[] = "💰 *Total: Rp " . $this->rupiah($total) . "*";

    return implode("\n", $lines);
  }

  // ─── Rekap Bulanan ──────────────────────────────────────────

  private function replyRekapBulanan(): string
  {
    $data = Expense::byNumber($this->from)
      ->thisMonth()
      ->selectRaw('kategori, SUM(nominal) as total, COUNT(*) as jumlah')
      ->groupBy('kategori')
      ->orderByDesc('total')
      ->get();

    if ($data->isEmpty()) {
      return "📭 Belum ada transaksi bulan ini.";
    }

    $bulan  = now()->locale('id')->translatedFormat('F Y');
    $lines  = ["📊 *Rekap " . $bulan . "*", "━━━━━━━━━━━━━━", ""];
    $total  = 0;

    foreach ($data as $item) {
      $lines[] = "• {$item->kategori} ({$item->jumlah}x)";
      $lines[] = "  Rp " . $this->rupiah($item->total);
      $total  += $item->total;
    }

    $lines[] = "";
    $lines[] = "━━━━━━━━━━━━━━";
    $lines[] = "💰 *Total: Rp " . $this->rupiah($total) . "*";

    return implode("\n", $lines);
  }

  // ─── Hapus Terakhir ─────────────────────────────────────────

  private function replyHapusTerakhir(): string
  {
    $terakhir = Expense::where('wa_number', $this->from)
      ->orderByDesc('recorded_at')
      ->first();

    if (!$terakhir) {
      return "📭 Tidak ada transaksi yang bisa dihapus.";
    }

    $jenisLabel = $terakhir->jenis_transaksi === 'masuk' ? 'Pemasukan' : 'Pengeluaran';
    $info = "[{$jenisLabel}] {$terakhir->keterangan} ({$terakhir->kategori}) — Rp " . $this->rupiah($terakhir->nominal);
    $terakhir->delete();

    $saldo = Expense::hitungSaldo($this->from);

    return implode("\n", [
      "🗑️ *Transaksi dihapus!*",
      "",
      "📝 {$info}",
      "",
      "💳 Sisa Total Saldo: *Rp " . $this->rupiah((int) $saldo) . "*",
    ]);
  }

  // ─── Bantuan ────────────────────────────────────────────────

  private function replyBantuan(): string
  {
    return implode("\n", [
      "🤖 *Panduan Chatbot Keuangan*",
      "━━━━━━━━━━━━━━",
      "*📝 Catat Pengeluaran:*",
      "keterangan (kategori) nominal",
      "• makan 50000",
      "• bensin 30rb",
      "• listrik bills 250000",
      "• beli kopi food 15k",
      "",
      "*💰 Catat Pemasukan:*",
      "keyword (deskripsi) nominal",
      "• gaji 5000000",
      "• gaji kantor 5jt",
      "• transfer ayah 500rb",
      "• masuk 100rb",
      "",
      "*📊 Rekap:*",
      "• rekap → hari ini",
      "• rekap minggu → minggu ini",
      "• rekap bulan → bulan ini",
      "",
      "*🗑️ Hapus:*",
      "• hapus → hapus transaksi terakhir",
      "",
      "*🔔 Reminder & Tagihan:*",
      "• tagihan → lihat daftar tagihan aktif",
      "• selesai [id] → bayar/lunasi tagihan",
      "• cicil [nominal] [tgl] → bayar cicilan tagihan",
      "",
      "*💡 Saran kategori pengeluaran:*",
      "food • transport • bills",
      "shopping • health • other",
      "",
    ]);
  }

  // ─── Reminder & Tagihan ─────────────────────────────────────

  private function replyDaftarTagihan(): string
  {
    $tagihan = Reminder::byNumber($this->from)
      ->whereIn('status', ['aktif', 'overdue'])
      ->where('sisa_tagihan', '>', 0)
      ->orderBy('tanggal_jatuh_tempo')
      ->get();

    if ($tagihan->isEmpty()) {
      return "🎉 *Tidak Ada Tagihan Aktif*\n\nSemua tagihan Anda lunas atau belum ada jadwal tagihan.";
    }

    $lines = ["📋 *Daftar Tagihan Aktif*", "━━━━━━━━━━━━━━", ""];
    $totalSisa = 0;

    foreach ($tagihan as $t) {
      $due = Carbon::parse($t->tanggal_jatuh_tempo);
      $isOverdue = $due->isPast() && !$due->isToday();
      $badge = $isOverdue ? "⚠️ *TELAT*" : "📅 " . $due->format('d/m/Y');

      $lines[] = "*[ID: {$t->id}]* {$t->nama_tagihan}";
      $lines[] = "💰 Sisa: Rp " . $this->rupiah($t->sisa_tagihan) . " | {$badge}";
      if ($t->keterangan) {
        $lines[] = "📝 {$t->keterangan}";
      }
      $lines[] = "";
      $totalSisa += $t->sisa_tagihan;
    }

    $lines[] = "━━━━━━━━━━━━━━";
    $lines[] = "💵 *Total Seluruh Tagihan: Rp " . $this->rupiah($totalSisa) . "*";
    $lines[] = "";
    $lines[] = "💡 *Aksi Langsung via WhatsApp:*";
    $lines[] = "• Ketik *SELESAI [ID]* untuk melunasi";
    $lines[] = "• Ketik *CICIL [ID] [Nominal] [Tgl_Baru]*";

    return implode("\n", $lines);
  }

  private function replyReminderLunas(array $data): string
  {
    $reminderId = $data['reminder_id'] ?? null;
    $reminder = null;

    if ($reminderId) {
      $reminder = Reminder::where('id', $reminderId)->where('wa_number', $this->from)->first();
    } else {
      $activeReminders = Reminder::byNumber($this->from)
        ->whereIn('status', ['aktif', 'overdue'])
        ->where('sisa_tagihan', '>', 0)
        ->orderBy('tanggal_jatuh_tempo')
        ->get();

      if ($activeReminders->count() === 1) {
        $reminder = $activeReminders->first();
      } elseif ($activeReminders->count() > 1) {
        $lines = [
          "⚠️ *Pilih Tagihan yang Ingin Dilunasi:*",
          "Ada lebih dari 1 tagihan aktif. Silakan tentukan ID tagihan:",
          ""
        ];
        foreach ($activeReminders as $r) {
          $lines[] = "• Ketik *SELESAI {$r->id}* → {$r->nama_tagihan} (Rp " . $this->rupiah($r->sisa_tagihan) . ")";
        }
        return implode("\n", $lines);
      }
    }

    if (!$reminder) {
      return "📭 Tagihan tidak ditemukan atau sudah lunas.\nKetik *tagihan* untuk melihat daftar tagihan aktif.";
    }

    $action = app(ReminderAction::class);
    $nominal = $reminder->sisa_tagihan;
    $result = $action->bayarTagihan($reminder, $nominal, null, 'whatsapp', 'Pelunasan via WhatsApp');

    if (!$result['is_valid']) {
      return "❌ Gagal memproses: " . ($result['message'] ?? 'Terjadi kesalahan.');
    }

    $saldo = Expense::hitungSaldo($this->from);

    return implode("\n", [
      "✅ *Tagihan LUNAS!*",
      "━━━━━━━━━━━━━━",
      "📌 *Tagihan:* {$reminder->nama_tagihan}",
      "💰 *Nominal Dibayar:* Rp " . $this->rupiah($nominal),
      "🏷️ *Kategori:* {$reminder->kategori}",
      "",
      "📊 Pembayaran otomatis dicatat di Laporan Keuangan.",
      "💳 Total Saldo Saat Ini: *Rp " . $this->rupiah((int) $saldo) . "*",
    ]);
  }

  private function replyReminderCicil(array $data): string
  {
    $reminderId = $data['reminder_id'] ?? null;
    $nominal    = $data['nominal'] ?? 0;
    $newDueDate = $data['new_due_date'] ?? null;

    if ($nominal <= 0) {
      return "❓ Nominal cicilan tidak valid.\nContoh: *CICIL 400000 25/10/2026*";
    }

    $reminder = null;
    if ($reminderId) {
      $reminder = Reminder::where('id', $reminderId)->where('wa_number', $this->from)->first();
    } else {
      $activeReminders = Reminder::byNumber($this->from)
        ->whereIn('status', ['aktif', 'overdue'])
        ->where('sisa_tagihan', '>', 0)
        ->orderBy('tanggal_jatuh_tempo')
        ->get();

      if ($activeReminders->count() === 1) {
        $reminder = $activeReminders->first();
      } elseif ($activeReminders->count() > 1) {
        $lines = [
          "⚠️ *Pilih Tagihan yang Ingin Dicicil:*",
          "Ada beberapa tagihan aktif. Sertakan ID tagihan:",
          ""
        ];
        foreach ($activeReminders as $r) {
          $lines[] = "• Ketik *CICIL {$r->id} {$nominal} " . ($newDueDate ?? '') . "* → {$r->nama_tagihan} (Sisa: Rp " . $this->rupiah($r->sisa_tagihan) . ")";
        }
        return implode("\n", $lines);
      }
    }

    if (!$reminder) {
      return "📭 Tagihan tidak ditemukan atau sudah lunas.\nKetik *tagihan* untuk melihat daftar tagihan aktif.";
    }

    $action = app(ReminderAction::class);
    $result = $action->bayarTagihan($reminder, $nominal, $newDueDate, 'whatsapp', 'Cicilan via WhatsApp');

    if (!$result['is_valid']) {
      return "❌ Gagal memproses: " . ($result['message'] ?? 'Terjadi kesalahan.');
    }

    $saldo = Expense::hitungSaldo($this->from);
    $statusMsg = $result['is_lunas'] ? "🎉 *LUNAS!*" : "Sisa Tagihan: *Rp " . $this->rupiah($result['sisa_tagihan']) . "*";

    $lines = [
      "✅ *Cicilan Berhasil Dicatat!*",
      "━━━━━━━━━━━━━━",
      "📌 *Tagihan:* {$reminder->nama_tagihan}",
      "💸 *Dibayar:* Rp " . $this->rupiah($result['nominal_bayar']),
      "📊 {$statusMsg}",
    ];

    if (!$result['is_lunas']) {
      if (!empty($result['jatuh_tempo_baru'])) {
        $lines[] = "📅 *Jatuh Tempo Baru:* " . $result['jatuh_tempo_baru'];
      } elseif ($reminder->perulangan !== 'tidak') {
        $lines[] = "📅 *Jatuh Tempo Periode Ini:* " . ($result['jatuh_tempo'] ?? $reminder->tanggal_jatuh_tempo?->format('d/m/Y'));
        $lines[] = "ℹ️ Tagihan berulang (" . ucfirst($reminder->perulangan) . "): periode berikutnya otomatis dibuat setelah lunas.";
      } elseif (!empty($result['jatuh_tempo'])) {
        $lines[] = "📅 *Jatuh Tempo:* " . $result['jatuh_tempo'];
      }
    }

    $lines[] = "";
    $lines[] = "📝 Pengeluaran telah otomatis masuk ke Laporan Keuangan.";
    $lines[] = "💳 Total Saldo: *Rp " . $this->rupiah((int) $saldo) . "*";

    return implode("\n", $lines);
  }

  // ─── Unknown ────────────────────────────────────────────────

  private function replyUnknown(): string
  {
    return implode("\n", [
      "❓ Format tidak dikenali.",
      "",
      "Gunakan: *keterangan kategori nominal*",
      "Contoh: _makan food 50000_",
      "",
      "Ketik *bantuan* untuk panduan.",
    ]);
  }

  // ─── Helper ─────────────────────────────────────────────────

  private function rupiah(int $nominal): string
  {
    return number_format($nominal, 0, ',', '.');
  }
}
