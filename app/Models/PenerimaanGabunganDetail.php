<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanGabunganDetail extends Model
{
  protected $table = 't_penerimaan_gabungan_dtl';

  public $timestamps = true;

  protected $fillable = [
    'id_header',
    'no_spk',
    'nama_customer',
    'nilai',
    'pph',
    'biaya_merimen',
  ];
}
