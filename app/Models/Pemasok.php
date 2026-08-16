<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
  protected $table = 'm_pemasok';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_pemasok',
    'nama_pemasok',
    'npwp',
    'alamat1',
    'alamat2',
    'kota',
    'kode_pos',
    'po_box',
    'telepon',
    'fax',
    'email',
    'kontak_person',
    'is_active',
    'file_npwp',
    'created_by',
    'updated_by'
  ];
}
