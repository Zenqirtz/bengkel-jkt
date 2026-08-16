<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangMukaPembelian extends Model
{
    protected $table = 't_uang_muka_pembelian';

    public $timestamps = true;

    protected $fillable = [
        'kode_cabang',
        'no_transaksi',
        'tanggal_transaksi',
        'jenis_pengeluaran',
        'nama',
        'kode_bank',
        'no_rekening',
        'nilai',
        'created_by',
        'updated_by',
    ];
}
