<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilePerusahaan extends Model
{
    protected $table = 'm_cabang';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang', 
        'nama_singkat', 
        'nama_cabang', 
        'alamat1',
        'alamat2',
        'alamat3',
        'kode_pos',
        'telepon',
        'fax',
        'npwp',
        'email',
        'logo_cabang',
        'nourut',
        'is_active',
        'kepala_bengkel',
        'workshop_manager',
        'kepala_lapangan',
        'rekening1',
        'rekening2',
        'created_by',
        'updated_by',
    ];

}
