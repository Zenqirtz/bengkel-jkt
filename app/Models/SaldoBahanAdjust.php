<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoBahanAdjust extends Model
{
  protected $table = 't_adjust_saldo_bahan';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'tanggal',
    'bulan',
    'tahun',
    'kode_bahan',
    'kode_group_bahan',
    'unit_adjust',
    'harga_adjust',
    'jumlah_adjust',
    'created_by',
    'updated_by'   
  ];
}
