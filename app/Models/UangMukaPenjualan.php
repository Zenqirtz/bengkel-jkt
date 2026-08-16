<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangMukaPenjualan extends Model
{
  protected $table = 't_uang_muka_penjualan';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_transaksi',
    'tanggal_transaksi',
    'jenis_penerimaan',
    'nama',
    'kode_bank',
    'no_rekening',
    'nilai',
    'created_by',
    'updated_by',
  ];
}
