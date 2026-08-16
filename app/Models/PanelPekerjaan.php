<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelPekerjaan extends Model
{
  protected $table = 'm_panel_pekerjaan';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_panel_pekerjaan',
    'panel_pekerjaan',
    'point',
    'no_panel',
    'harga',
    'is_active',
    'created_by',
    'updated_by'
  ];
}
