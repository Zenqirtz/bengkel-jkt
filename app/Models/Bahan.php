<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
  protected $table = 'm_bahan';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_bahan',
    'nama_bahan',
    'kode_group_bahan',
    'kode_satuan',
    'kode_satuan2',
    'konversi',
    'harga',
    'harga_konversi',
    'no_bahan',
    'is_active',
    'created_by',
    'updated_by'
  ];
}
