<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranGabunganDetail extends Model
{
  protected $table = 't_pembayaran_gabungan_dtl';

  public $timestamps = true;

  protected $fillable = [
    'id_header',
    'kode_input',
    'no_bon_toko',
    'nilai',
  ];
}
