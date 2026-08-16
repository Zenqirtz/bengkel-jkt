<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimasiLain extends Model
{
  protected $table = 't_estimasi_dtl3';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_estimasi',
    'idx',
    'memo',
    'harga',
    'tipe',
    'harga_s',
    'created_by',
    'updated_by',
  ];
}
