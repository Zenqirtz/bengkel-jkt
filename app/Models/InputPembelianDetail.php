<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputPembelianDetail extends Model
{
  protected $table = 't_input_gudang_dtl';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_input',
    'seq_no',
    'kode_bahan',
    'qty',
    'kode_satuan',
    'harga',
    'memo',
    'jumlah',
    'no_sparepart',
    'kode_spk',
    'diskon',
    'harga_diskon',
    'ppn',
    'jumlah_sebelum',
    'cek',
    'created_by',
    'updated_by'
  ];
}
