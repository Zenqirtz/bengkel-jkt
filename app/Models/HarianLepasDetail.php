<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HarianLepasDetail extends Model
{
  protected $table = 't_harian_lepas_dtl';

  public $timestamps = true;

  protected $fillable = [
    'id_header',
    'kode_spk',
    'no_polisi',
    // 'nama_pemilik',
    'nama_tipe',
    'upah',
    'sisa',
    'persen',
    'nilai',
  ];
}
