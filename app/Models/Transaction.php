<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{

  protected $fillable = [
    'wa_number',
    'keterangan',
    'kategori',
    'nominal',
    'recorded_at',
    'jenis_transaksi',
  ];

  protected $table = 'expenses';


  public function scopeGetCategories(Builder $query): Builder
  {
    return $query->select('kategori')->distinct();
  }
}
