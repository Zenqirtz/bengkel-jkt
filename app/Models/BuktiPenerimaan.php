<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPenerimaan extends Model
{
    protected $table = 't_transaksi_masuk_hdr';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang',
        'no_transaksi',
        'tanggal_transaksi',
        'kode_kategori',
        'no_voucher',
        'kode_pelanggan',
        'memo',
        'cabang_id',
        'kode_bank',
        'tanggal_ch_bg',
        'no_ch_bg',
        'total',
        'no_kliring',
        'tanggal_kliring',
        'no_voucher_cabang',
        'kode_bank_asal',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
