<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPengeluaran extends Model
{
    protected $table = 't_transaksi_keluar_hdr';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang',
        'no_transaksi',
        'tanggal_transaksi',
        'kode_kategori',
        'no_voucher',
        'kode_pemasok',
        'memo',
        'cabang_id',
        'kode_bank',
        'tanggal_ch_bg',
        'no_ch_bg',
        'total',
        'no_kliring',
        'tanggal_kliring',
        'cetak_id',
        'jenis_cetak_id',
        'lewat_ho_id',
        'nama_ch_bg',
        'no_voucher_cabang',
        'jenis_ch_bg',
        'kode_bank_asal',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
