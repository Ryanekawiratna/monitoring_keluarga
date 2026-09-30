<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderPaymentHistory extends Model
{
  use HasFactory;

  protected $fillable = [
    'reminder_id',
    'expense_id',
    'nominal_bayar',
    'tanggal_bayar',
    'sumber',
    'catatan',
  ];

  protected $casts = [
    'nominal_bayar' => 'integer',
    'tanggal_bayar' => 'datetime',
  ];

  public function reminder(): BelongsTo
  {
    return $this->belongsTo(Reminder::class, 'reminder_id');
  }

  public function expense(): BelongsTo
  {
    return $this->belongsTo(Expense::class, 'expense_id');
  }
}
