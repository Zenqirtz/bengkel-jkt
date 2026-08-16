<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerekKendaraan extends Model
{
  protected $table = 'm_merek_kendaraan';

  public $timestamps = true;

  protected $fillable = [
    'kode_merek',
    'nama_merek',
    'is_active', 
    'created_by',
    'updated_by'
  ];
}
