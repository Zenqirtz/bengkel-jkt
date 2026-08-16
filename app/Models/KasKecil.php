<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKecil extends Model
{
  protected $table = 't_input_kas';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_voucher',
    'tanggal',
    'jenis',
    'transaksi',
    // 'no_inv_single',
    'no_inv_gabung',
    'nilai',
    // 'jml_dibayar',
    'dp',
    'no_uang_muka',
    'pph',
    'biaya_merimen',
    'biaya_admin',
    'sisa',
    'account_coa',
    'no_spk',
    'keterangan',
    // 'status',
    'created_by',
    'updated_by',
  ];
}
