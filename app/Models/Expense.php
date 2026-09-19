<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
  protected $fillable = [
    'wa_number',
    'keterangan',
    'kategori',
    'nominal',
    'recorded_at',
    'jenis_transaksi',
  ];

  protected $casts = [
    'recorded_at' => 'datetime',
    'nominal'     => 'integer',
  ];

  public function scopeByNumber(Builder $query, string $number): Builder
  {
    return $query->where('wa_number', $number)->where('jenis_transaksi', 'keluar');
  }

  public static function hitungSaldo(string $number): float
  {
    $total_masuk = self::where('wa_number', $number)
      ->where('jenis_transaksi', 'masuk')
      ->sum('nominal');

    $total_keluar = self::where('wa_number', $number)
      ->where('jenis_transaksi', 'keluar')
      ->sum('nominal');

    return (float) ($total_masuk - $total_keluar);
  }

  public function scopePemasukan($query, string $number)
  {
    $total_masuk_hari_ini = $query->where('wa_number', $number)->where('jenis_transaksi', 'masuk')->today()->sum('nominal');
    return $total_masuk_hari_ini;
  }

  public function scopeToday(Builder $query): Builder
  {
    return $query->whereDate('recorded_at', today());
  }

  public function scopeThisWeek(Builder $query): Builder
  {
    return $query->whereBetween('recorded_at', [
      now()->startOfWeek(),
      now()->endOfWeek(),
    ]);
  }

  public function scopeThisMonth(Builder $query, ?string $jenis_transaksi = null): Builder
  {
    if ($jenis_transaksi !== null) {
      $query->where('jenis_transaksi', $jenis_transaksi);
    }

    return $query->whereYear('recorded_at', now()->year)
      ->whereMonth('recorded_at', now()->month);
  }

  public function scopeByMonth(Builder $query, int $year, int $month): Builder
  {
    return $query->whereYear('recorded_at', $year)
      ->whereMonth('recorded_at', $month);
  }
}
