<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenomoranTransaksi extends Model
{
    protected $table = 'm_penomoran';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang', 
        'tahun', 
        'bulan', 
        'modul', 
        'cabang', 
        'bank', 
        'digit_cnt', 
        'nourut', 
        'segmen1', 
        'segmen2', 
        'segmen3', 
        'segmen4', 
        'segmen5', 
        'segmen6', 
        'segmen7', 
        'autoreset', 
        'contoh', 
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];
}
