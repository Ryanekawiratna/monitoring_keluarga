<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Categorie extends Model
{

  public function scopeGetCategories(Builder $query): Builder
  {
    return $query;
  }
}
