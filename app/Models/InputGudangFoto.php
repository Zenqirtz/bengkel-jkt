<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputGudangFoto extends Model
{
  protected $table = 't_input_gudang_photo';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_input',
    'no_urut',
    'nama_file',
    'photo_bon',
    'ukuran',
    'created_by',
    'updated_by',
  ];
}
