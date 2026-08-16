<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPembelianDetail extends Model
{
  protected $table = 't_order_dtl1';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_order',
    'seq_no',
    'kode_bahan',
    'qty',
    'kode_satuan',
    'harga',
    'memo',
    'jumlah',
    'no_sparepart',
    'cek',
    'created_by',
    'updated_by'    
  ];
}
