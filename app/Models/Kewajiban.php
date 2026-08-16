<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kewajiban extends Model
{
  protected $table = 't_kewajiban_tertanggung';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_spk',
    'kode_estimasi',
    'kode_pelanggan',
    'cek_polis',
    'surat_kuasa',
    'prorata',
    'pernyataan_puas',
    'biaya_penyusutan',
    'is_free',
    'nilai_free_or',
    'biaya_komisi',
    'biaya_estimasi',
    'biaya_pribadi',
    'keterangan',
    'tgl_kewajiban',
    'created_by',
    'updated_by'
  ];
}
