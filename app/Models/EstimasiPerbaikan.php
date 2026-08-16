<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimasiPerbaikan extends Model
{
  protected $table = 't_estimasi_dtl1';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_estimasi',
    'idx',
    'kode_jenis_pekerjaan',
    'kode_panel_pekerjaan',
    'harga',
    'tipe',
    'harga_s',
    'created_by',
    'updated_by',  
  ];
}
