<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
  protected $table = 'm_sparepart';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_sparepart',
    'nama_sparepart',
    'kode_satuan',
    'price',
    'is_active',
    'created_by',
    'updated_by'
  ];
}
