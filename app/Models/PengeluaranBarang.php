<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranBarang extends Model
{
  protected $table = 't_pengeluaran_barang_hdr';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_pengeluaran',
    'seq_no',
    'tgl_pengeluaran',
    'kode_permintaan',
    'kode_input',
    'no_bon',
    'tipe',
    'memo',
    'kode_spk',
    'kode_bagian',
    'pengulangan',
    'status_approve',
    'tgl_approve',
    'user_approve',
    'created_by',
    'updated_by'
  ];
}
