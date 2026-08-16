<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsepEstimasiPerbaikan extends Model
{
  protected $table = 't_konsep_estimasi_dtl1';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_konsep_estimasi',
    'idx',
    'kode_jenis_pekerjaan',
    'kode_panel_pekerjaan',
    'harga',
    'tipe',
    'created_by',
    'updated_by',    
  ];
}
