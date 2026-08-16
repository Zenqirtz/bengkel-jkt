<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KirimKwitansi extends Model
{
  protected $table = 't_kirim_kwitansi';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_kirim_kwitansi',
    'kode_spk',
    'tanggal',
    'kode_kwitansi',
    'memo',
    'created_by',
    'updated_by'    
  ];
}
