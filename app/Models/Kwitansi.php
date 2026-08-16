<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
  protected $table = 't_kwitansi';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_kwitansi',
    'tanggal',
    'kode_estimasi',
    'kode_spk',
    'persen_jasa',
    'persen_bahan',
    'total_perbaikan',
    'total_sparepart',
    'total_lain',
    'total_or_ass',
    'grand_total',
    'ppn',
    'memo',
    'prorata',
    'pph',
    'kode_tipe_kwitansi',
    'kode_kirim_kwitansi',
    'tgl_kirim_kwitansi',
    'salvage',
    'penyusutan',
    'discount',
    'transport',
    'is_bayar',
    'is_faktur',
    'created_by',
    'updated_by'    
  ];
}
