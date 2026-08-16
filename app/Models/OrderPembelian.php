<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPembelian extends Model
{
  protected $table = 't_order_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_order',
    'tanggal',
    'batal',
    'no_po',
    'kode_pemasok',
    'memo',
    'ppn',
    'total',
    'tipe_barang',
    'kode_spk',
    'tipe_bayar',
    'kode_permintaan',
    'sifat_ppn',
    'status_approve',
    'tgl_approve',
    'user_approve',
    'memo_batal',
    'tgl_batal',
    'user_batal',
    'created_by',
    'updated_by'
  ];
}
