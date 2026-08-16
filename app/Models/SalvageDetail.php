<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalvageDetail extends Model
{
  protected $table = 't_salvage_dtl';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'no_salvage',
    'line_no',
    'kode_sparepart',
    'qty',
    'cek',
    'created_by',
    'updated_by'
  ];
}
