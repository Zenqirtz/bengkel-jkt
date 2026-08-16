<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'm_karyawan';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang', 
        'kode_karyawan', 
        'no_absen',
        'nik',
        'nama',
        'alamat',
        'no_hp',
        'status_pajak',
        'status_karyawan',
        'status_aktif',
        'tgl_masuk',
        'tgl_keluar',
        'kode_posisi',
        'kode_jabatan',
        'file_photo',
        'file_ktp',
        'file_ttd',
        'photo',
        'photo_ktp',
        'photo_ttd',
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];

}
