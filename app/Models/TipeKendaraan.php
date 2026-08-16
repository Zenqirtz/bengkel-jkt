<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeKendaraan extends Model
{
  protected $table = 'm_tipe_kendaraan';

  public $timestamps = true;

  protected $fillable = [
    'kode_tipe',
    'kode_merek',
    'kode_jenis',
    'nama_tipe',
    'is_active', 
    'created_by',
    'updated_by'
  ];
}
