<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
  // protected $table = 'm_marketing';
  protected $table = 'm_karyawan';

  public $timestamps = true;

  // protected $fillable = [
  //   'kode_cabang',
  //   'kode_marketing',
  //   'nama_marketing',
  //   'no_identitas',
  //   'tipe_marketing',
  //   'is_active',
  //   'created_by',
  //   'updated_by'
  // ];

  protected $fillable = [
    'kode_cabang',
    'kode_karyawan',
    'nama',
    'no_hp',
    'kode_jabatan',
    'status_aktif',
    'created_by',
    'updated_by'
  ];
}
