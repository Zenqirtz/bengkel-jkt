<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranBarangDetail extends Model
{
  protected $table = 't_pengeluaran_barang_dtl';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'seq_no',
    'no_urut',
    'kode_barang',
    'qty',
    'harga',
    'jumlah',
    'kode_input',
    'kode_satuan',
    'no_sparepart',
    'created_by',
    'updated_by'    
  ];
}
