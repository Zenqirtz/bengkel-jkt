<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPengeluaranDetail extends Model
{
    protected $table = 't_transaksi_keluar_dtl';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang',
        'no_transaksi',
        'uraian',
        'kode_spk',
        'no_kuitansi',
        'jumlah',
        'created_by',
        'updated_by'
    ];

}
