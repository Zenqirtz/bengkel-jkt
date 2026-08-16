<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsepEstimasi extends Model
{
  protected $table = 't_konsep_estimasi_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'seq_id',
    'kode_konsep_estimasi',
    'kode_pelanggan',
    'tanggal',
    'kode_spk',
    'tahun',
    'kode_estimator',
    'lama_pekerjaan',
    'nama_surveyor',
    'tgl_survey',
    'total_perbaikan',
    'total_sparepart',
    'total_lain',
    'total',
    'memo',
    'created_by',
    'updated_by',
  ];
}
