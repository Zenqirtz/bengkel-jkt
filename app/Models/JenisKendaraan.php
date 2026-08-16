<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisKendaraan extends Model
{
  protected $table = 'm_jenis_kendaraan';

  public $timestamps = true;

  protected $fillable = [
    'kode_jenis',
    'nama_jenis',
    'is_active', 
    'created_by',
    'updated_by'
  ];
}
