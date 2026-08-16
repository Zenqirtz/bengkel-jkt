<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelangganFinance extends Model
{
    protected $table = 'm_pelanggan_fin';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'kontak_person',
        'handphone',
        'alamat',
        'kode_kota',
        'kode_pos',
        'telepon',
        'fax',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
