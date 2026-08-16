<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPembelianDetail2 extends Model
{
  protected $table = 't_order_dtl2';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_order',
    'seq_no',
    'kode_spk',
    'kode_sparepart',
    'qty',
    'kode_satuan',
    'harga',
    'memo',
    'jumlah',
    'no_sparepart',
    'created_by',
    'updated_by'    
  ];
}
