<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Umum extends Model
{
  protected $table = 'm_umum';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_barang',
    'nama_barang',
    'kode_jenis',
    'price',
    'is_active',
    'created_by',
    'updated_by'
  ];
}
