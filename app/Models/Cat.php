<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
  protected $table = 'm_rasio_cat';
  
  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_bahan',
    'jenis',
    'rasio',
    'created_by',
    'updated_by'
  ];
}
