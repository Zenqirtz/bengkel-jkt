<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
  protected $table = 't_retur_barang_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_retur',
    'tanggal',
    'seq_no',
    'kode_pemasok',
    'tipe',
    'kode_input',
    'kode_spk',
    'tipe_bayar',
    'no_bon',
    'memo',
    'status_approve',
    'tgl_approve',
    'user_approve',
    'created_by',
    'updated_by'
  ];
}
