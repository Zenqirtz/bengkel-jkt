<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputPembelian extends Model
{
  protected $table = 't_input_gudang_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_input',
    'tanggal',
    'kode_order',
    'no_po',
    'no_input',
    'kode_pemasok',
    'memo',
    'tipe',
    'is_bayar',
    'tanggal_jt',
    'sifat_ppn',
    'kode_spk',
    'no_bon',
    'tipe_bayar',
    'ppn',
    'diskon',
    'total',
    'status_approve',
    'tgl_approve',
    'user_approve',
    'created_by',
    'updated_by'
  ];
}
