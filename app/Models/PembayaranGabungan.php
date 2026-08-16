<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranGabungan extends Model
{
    protected $table = 't_pembayaran_gabungan_hdr';

    public $timestamps = true;

    protected $fillable = [
        'kode_cabang',
        'no_transaksi',
        'tanggal_transaksi',
        'jenis_pembayaran',
        'kode_pemasok',
        'nama_supplier',
        'kode_bank',
        'no_rekening',
        'total_nilai',
        'created_by',
        'updated_by',
    ];
}
