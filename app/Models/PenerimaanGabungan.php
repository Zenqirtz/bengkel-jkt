<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanGabungan extends Model
{
  protected $table = 't_penerimaan_gabungan_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_transaksi',
    'tanggal_transaksi',
    'jenis_pembayaran',
    'kode_pelanggan',
    'nama_customer',
    'kode_bank',
    'no_rekening',
    'total_nilai',
    'pph',
    'biaya_merimen',
    'created_by',
    'updated_by',
  ];
}
