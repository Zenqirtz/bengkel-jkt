<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpkFoto extends Model
{
  protected $table = 't_spk_photo';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_spk',
    'no_urut',
    'nama_panel',
    'photo_panel',
    'ukuran',
    'created_by',
    'updated_by'
  ];
}
