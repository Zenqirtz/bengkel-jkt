<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'm_bank_fin';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang',
        'kode_bank',
        'nama_bank',
        'kode_kategori',
        'trx_code',
        'is_active',
        'no_rekening',
        'lokasi_bank',
        'created_by',
        'updated_by',
    ];

}
