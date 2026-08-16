<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salvage extends Model
{
  protected $table = 't_salvage_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_spk',
    'no_salvage',
    'tanggal',
    'no_polisi',
    'no_polis',
    'kode_merek',
    'kode_tipe',
    'kode_pelanggan',
    'no_pengiriman',
    'tgl_kirim',
    'pengirim',
    'penerima',
    'created_by',
    'updated_by'    
  ];
}
