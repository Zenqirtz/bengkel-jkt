<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perantara extends Model
{
  protected $table = 'm_perantara_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_perantara',
    'nama_perantara',
    'kode_jenis_perantara',
    'status',
    'telepon',
    'is_active',
    'created_by',
    'updated_by'
  ];
}
