<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointPanel extends Model
{
  protected $table = 't_point_panel';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_spk',
    'seq_id',
    'kode_jenis_pekerjaan',
    'kode_panel_pekerjaan',
    'point',
    'cek',
    'created_by',
    'updated_by'
  ];
}
