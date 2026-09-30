<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Reminder extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'wa_number',
    'nama_tagihan',
    'jenis_transaksi',
    'total_nominal',
    'sisa_tagihan',
    'tanggal_jatuh_tempo',
    'kategori',
    'perulangan',
    'keterangan',
    'status',
    'last_notified_at',
  ];

  protected $casts = [
    'tanggal_jatuh_tempo' => 'date',
    'total_nominal'       => 'integer',
    'sisa_tagihan'        => 'integer',
    'last_notified_at'    => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function payments(): HasMany
  {
    return $this->hasMany(ReminderPaymentHistory::class, 'reminder_id')->orderByDesc('tanggal_bayar');
  }

  public function scopeActive(Builder $query): Builder
  {
    return $query->whereIn('status', ['aktif', 'overdue']);
  }

  public function scopeByNumber(Builder $query, string $number): Builder
  {
    return $query->where('wa_number', $number);
  }

  public function scopeDueSoon(Builder $query, int $days = 3): Builder
  {
    return $query->where('status', 'aktif')
      ->whereBetween('tanggal_jatuh_tempo', [today(), today()->addDays($days)]);
  }

  public function scopeOverdue(Builder $query): Builder
  {
    return $query->where('status', 'aktif')
      ->where('tanggal_jatuh_tempo', '<', today());
  }

  /**
   * Helper status visual badge & label
   */
  public function getStatusLabelAttribute(): string
  {
    if ($this->status === 'lunas') {
      return 'Lunas';
    }
    if ($this->status === 'nonaktif') {
      return 'Nonaktif';
    }
    if ($this->tanggal_jatuh_tempo && $this->tanggal_jatuh_tempo->isPast() && !$this->tanggal_jatuh_tempo->isToday()) {
      return 'Overdue (Telat)';
    }
    return 'Aktif';
  }

  public function getStatusColorAttribute(): string
  {
    if ($this->status === 'lunas') {
      return 'success';
    }
    if ($this->status === 'nonaktif') {
      return 'secondary';
    }
    if ($this->tanggal_jatuh_tempo && $this->tanggal_jatuh_tempo->isPast() && !$this->tanggal_jatuh_tempo->isToday()) {
      return 'danger';
    }
    return 'primary';
  }
}
