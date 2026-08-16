<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoSparepartAdjust extends Model
{
  protected $table = 't_adjust_saldo_sparepart';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'tanggal',
    'bulan',
    'tahun',
    'kode_merek',
    'kode_tipe',
    'kode_input',
    'kode_sparepart',
    'unit_adjust',
    'harga_adjust',
    'jumlah_adjust',
    'created_by',
    'updated_by'
  ];
}
