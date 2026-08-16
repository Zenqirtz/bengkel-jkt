<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianBahan extends Model
{
  protected $table = 'm_standarisasi_point_panel';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_posisi',
    'kode_bahan',
    'point_panel',
    'qty',
    'created_by',
    'updated_by'
  ];
}
