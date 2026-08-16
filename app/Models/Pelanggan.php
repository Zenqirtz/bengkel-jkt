<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
  protected $table = 'm_pelanggan_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_pelanggan',
    'nama_pelanggan',
    'kode_jenis_pelanggan',
    'status',
    'kode_kelas',
    'alamat1',
    'alamat2',
    'kota',
    'kode_pos',
    'po_box',
    'telepon',
    'fax',
    'email',
    'kode_marketing',
    'contact_person',
    'is_active',
    'npwp',
    'file_npwp',
    'created_by',
    'updated_by'
  ];
}
