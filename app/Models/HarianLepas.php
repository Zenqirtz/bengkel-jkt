<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HarianLepas extends Model
{
  protected $table = 't_harian_lepas_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_transaksi',
    'tanggal_transaksi',
    'jenis_pekerjaan',
    'kode_karyawan',
    'nama_pekerja',
    'kode_bank',
    'no_rekening',
    'total_nilai',
    'created_by',
    'updated_by',
  ];
}
