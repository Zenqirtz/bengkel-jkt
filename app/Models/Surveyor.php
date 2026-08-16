<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surveyor extends Model
{
  protected $table = 'm_surveyor';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_surveyor',
    'nama_surveyor',
    'is_active',
  ];
}
