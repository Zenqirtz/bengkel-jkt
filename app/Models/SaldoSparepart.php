<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoSparepart extends Model
{
  protected $table = 't_saldo_sparepart';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'periode_bulan',
    'periode_tahun',
    'bulan',
    'tahun',
    'kode_merek',
    'kode_tipe',
    'kode_input',
    'kode_sparepart',
    'unit_awal',
    'harga_awal',
    'jumlah_awal',
    'unit_tambah',
    'harga_tambah',
    'jumlah_tambah',
    'unit_kurang',
    'harga_kurang',
    'jumlah_kurang',
    'unit_retur',
    'harga_retur',
    'jumlah_retur',
    'unit_adjust',
    'harga_adjust',
    'jumlah_adjust',
    'unit_akhir',
    'harga_akhir',
    'jumlah_akhir',
    'unit_so',
    'harga_so',
    'jumlah_so',
    'unit_selisih',
    'jumlah_selisih',
    'created_by',
    'updated_by'
  ];
}
