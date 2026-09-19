<?php

namespace App\Actions;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class DashboardAction
{
  /**
   * Mengambil seluruh data statistik untuk halaman Dashboard
   */
  public function getDashboardData($bulan, $tahun)
  {
    // ─── Kartu Statistik ─────────────────────────────────────
    $totalHariIni        = Expense::today()->sum('nominal');
    $totalBulanIni       = Expense::thisMonth('keluar')->sum('nominal');
    $totalMasukBulanIni  = Expense::thisMonth('masuk')->sum('nominal');
    $totalTransaksi      = Expense::thisMonth('keluar')->count();

    $penggunnaAktif      = Expense::thisMonth('keluar')->distinct('wa_number')->count('wa_number');

    // ─── Rekap Per Kategori (bulan dipilih) ──────────────────
    $perKategori = Expense::byMonth($tahun, $bulan)
      ->selectRaw('kategori, SUM(nominal) as total, COUNT(*) as jumlah')
      ->groupBy('kategori')
      ->orderByDesc('total')
      ->get();

    // ─── Tren Harian (30 hari terakhir) ──────────────────────  
    $trenHarian = Expense::where('recorded_at', '>=', now()->subDays(29)->startOfDay())
      ->selectRaw("DATE(recorded_at) as tanggal, SUM(nominal) as total")
      ->groupByRaw("DATE(recorded_at)")
      ->orderBy('tanggal')
      ->get()
      ->keyBy('tanggal');

    // Lengkapi hari yang kosong
    $labels = [];
    $values = [];
    for ($i = 29; $i >= 0; $i--) {
      $date     = now()->subDays($i)->format('Y-m-d');
      $labels[] = now()->subDays($i)->format('d/m');
      $values[] = $trenHarian->get($date)?->total ?? 0;
    }

    // ─── Transaksi Terbaru ───────────────────────────────────
    $transaksiTerbaru = Expense::orderByDesc('recorded_at')
      ->limit(10)
      ->get();

    // ─── Top Pengguna Bulan Ini ──────────────────────────────
    $topPengguna = Expense::thisMonth('keluar')
      ->selectRaw('wa_number, SUM(nominal) as total, COUNT(*) as jumlah')
      ->groupBy('wa_number')
      ->orderByDesc('total')
      ->limit(5)
      ->get();

    // ─── Daftar Bulan untuk Filter ───────────────────────────
    $daftarBulan = [];
    for ($i = 0; $i < 12; $i++) {
      $d = now()->subMonths($i);
      $daftarBulan[] = [
        'value' => $d->month . '-' . $d->year,
        'label' => $d->locale('id')->translatedFormat('F Y'),
      ];
    }

    // Kembalikan semua data dalam bentuk Array menggunakan compact()
    return compact(
      'totalHariIni',
      'totalBulanIni',
      'totalMasukBulanIni',
      'totalTransaksi',
      'penggunnaAktif',
      'perKategori',
      'labels',
      'values',
      'transaksiTerbaru',
      'topPengguna',
      'daftarBulan'
    );
  }
}
