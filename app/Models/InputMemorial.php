<?php
// app/Models/InputMemorial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputMemorial extends Model
{
  protected $table = 't_input_memorial';
  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_voucher',
    'tanggal',
    'jenis',
    'transaksi',
    'tipe',
    'no_spk',
    'no_invoice',
    'nama_supplier',
    'no_ig',
    'no_bon_toko',
    'nilai',
    'jml_dibayar',
    'sisa',
    'account_coa',
    'keterangan',
    // 'status',
    'created_by',
    'updated_by',
  ];
}
