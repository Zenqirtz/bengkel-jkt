<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemasokFinance extends Model
{
  protected $table = 'm_pemasok_fin';

  public $timestamps = true;

  protected $fillable = [
    'kode_pemasok',
    'nama_pemasok',
    'kontak_person',
    'handphone',
    'alamat',
    'kode_kota',
    'kode_pos',
    'telepon',
    'fax',
    'is_active',
    'created_at',
    'created_by',
    'updated_at',
    'updated_by'
  ];
}
