<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanBarangDetail extends Model
{
  protected $table = 't_permintaan_barang_dtl';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'seq_no',
    'no_urut',
    'kode_barang',
    'qty',
    'harga',
    'kode_analisa',
    'no_sparepart',
    'tipe',
    'created_by',
    'updated_by'    
  ];
}
