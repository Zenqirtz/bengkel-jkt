<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
  protected $table = 'm_mobil';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_polisi',
    'kode_pemilik',
    'nama_distnk',
    'kode_merek',
    'no_mesin',
    'no_rangka',
    'kode_tipe',
    'tipe_stnk',
    'tahun',
    'ukuran_cc',
    'jenis',
    'kode_jenis_perseneling',
    'warna',
    'kode_bahan_bakar',
    'kode_mesin',
    'kode_penggerak_roda',
    'tgl_stnk_berakhir',
    'no_model',
    'created_by',
    'updated_by'
  ];
}
