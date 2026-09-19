<?php

namespace App\Jobs;

use App\Models\Expense;
use App\Services\KirimDevService;
use App\Services\MessageParser;
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
  public int $backoff = 10;

  public function __construct(
    private readonly string $from,
    private readonly string $text,
    private readonly string $messageId,
  ) {}

  public function handle(MessageParser $parser, KirimDevService $kirimdev): void
  {
    Log::info('Processing WA message', ['from' => $this->from, 'text' => $this->text]);

    $parsed = $parser->parse($this->text);

    $reply = match ($parsed['type']) {
      'transaction' => $this->handleTransaction($parsed['data']),
      'command'     => $this->handleCommand($parsed['intent']),
      default       => $this->replyUnknown(),
    };

    $kirimdev->sendText($this->from, $reply);
  }

  // ─── Catat Transaksi ────────────────────────────────────────

  private function handleTransaction(array $data): string
  {
    Expense::create([
      'wa_number'   => $this->from,
      'keterangan'  => $data['keterangan'],
      'kategori'    => $data['kategori'],
      'nominal'     => $data['nominal'],
      'recorded_at' => now(),
    ]);

    $totalHariIni = Expense::byNumber($this->from)->today()->sum('nominal');

    return implode("\n", [
      "✅ *Tercatat!*",
      "",
      "📝 {$data['keterangan']} ({$data['kategori']})",
      "💰 Rp " . $this->rupiah($data['nominal']),
      "",
      "📊 Total hari ini: *Rp " . $this->rupiah($totalHariIni) . "*",
    ]);
  }

  // ─── Command Router ─────────────────────────────────────────

  private function handleCommand(string $intent): string
  {
    return match ($intent) {
      'rekap_harian'   => $this->replyRekapHarian(),
      'rekap_mingguan' => $this->replyRekapMingguan(),
      'rekap_bulanan'  => $this->replyRekapBulanan(),
      'hapus_terakhir' => $this->replyHapusTerakhir(),
      'bantuan'        => $this->replyBantuan(),
      default          => $this->replyUnknown(),
    };
  }

  // ─── Rekap Harian ───────────────────────────────────────────

  private function replyRekapHarian(): string
  {
    $transaksi = Expense::byNumber($this->from)
      ->today()
      ->orderBy('recorded_at')
      ->get();

    if ($transaksi->isEmpty()) {
      return "📭 Belum ada transaksi hari ini.\n\nMulai catat:\n*keterangan kategori nominal*";
    }

    $lines = ["📋 *Rekap Hari Ini*", "━━━━━━━━━━━━━━", ""];
    $total = 0;

    foreach ($transaksi as $t) {
      $jam     = $t->recorded_at->format('H:i');
      $lines[] = "{$jam} • {$t->keterangan} ({$t->kategori})";
      $lines[] = "       Rp " . $this->rupiah($t->nominal);
      $total  += $t->nominal;
    }

    $lines[] = "";
    $lines[] = "━━━━━━━━━━━━━━";
    $lines[] = "💰 *Total: Rp " . $this->rupiah($total) . "*";

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
    $terakhir = Expense::byNumber($this->from)
      ->orderByDesc('recorded_at')
      ->first();

    if (!$terakhir) {
      return "📭 Tidak ada transaksi yang bisa dihapus.";
    }

    $info = "{$terakhir->keterangan} ({$terakhir->kategori}) — Rp " . $this->rupiah($terakhir->nominal);
    $terakhir->delete();

    return implode("\n", [
      "🗑️ *Transaksi dihapus!*",
      "",
      "📝 {$info}",
    ]);
  }

  // ─── Bantuan ────────────────────────────────────────────────

  private function replyBantuan(): string
  {
    return implode("\n", [
      "🤖 *Panduan Chatbot Keuangan*",
      "━━━━━━━━━━━━━━",
      "",
      "*📝 Catat Pengeluaran:*",
      "keterangan kategori nominal",
      "",
      "*Contoh:*",
      "makan food 50000",
      "bensin transport 80.000",
      "listrik bills 250000",
      "",
      "*📊 Rekap:*",
      "• rekap → hari ini",
      "• rekap minggu → minggu ini",
      "• rekap bulan → bulan ini",
      "",
      "*🗑️ Hapus:*",
      "• hapus → hapus transaksi terakhir",
      "",
      "*💡 Saran kategori:*",
      "food • transport • bills",
      "shopping • health • other",
    ]);
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
