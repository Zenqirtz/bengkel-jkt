<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimator extends Model
{
  protected $table = 'm_estimator';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_estimator',
    'nama_estimator',
    'is_active',
  ];
}
