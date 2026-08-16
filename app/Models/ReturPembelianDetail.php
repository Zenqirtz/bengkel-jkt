<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetail extends Model
{
  protected $table = 't_retur_barang_dtl';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'seq_no',
    'line_no',
    'kode_barang',
    'qty',
    'kode_satuan',
    'harga',
    'jumlah',
    'kode_spk',
    'memo',
    'no_sparepart',
    'cek',
    'created_by',
    'updated_by'
  ];
}
