<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimasiSparepart extends Model
{
  protected $table = 't_estimasi_dtl2';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_estimasi',
    'idx',
    'kode_sparepart',
    'no_sparepart',
    'qty',
    'harga',
    'up',
    'jumlah',
    'tipe',
    'jumlah_s',
    'created_by',
    'updated_by',
  ];
}
