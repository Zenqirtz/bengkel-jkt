<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsepEstimasiLain extends Model
{
  protected $table = 't_konsep_estimasi_dtl3';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_konsep_estimasi',
    'idx',
    'memo',
    'harga',
    'tipe',
    'created_by',
    'updated_by',    
  ];
}
