<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanBarang extends Model
{
  protected $table = 't_permintaan_barang_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_permintaan',
    'seq_no',
    'tanggal_permintaan',
    'kode_spk',
    'tipe_barang',
    'kode_bagian',
    'jenis_permintaan',
    'pengulangan',
    'sudah_terpakai',
    'created_by',
    'updated_by'
  ];
}
