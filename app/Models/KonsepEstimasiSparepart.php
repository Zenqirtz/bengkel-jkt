<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsepEstimasiSparepart extends Model
{
  protected $table = 't_konsep_estimasi_dtl2';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_konsep_estimasi',
    'idx',
    'kode_sparepart',
    'no_sparepart',
    'qty',
    'harga',
    'up',
    'jumlah',
    'tipe',
    'created_by',
    'updated_by',    
  ];
}
