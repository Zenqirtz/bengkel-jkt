<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemilik extends Model
{
    protected $table = 'm_pemilik_hdr';

    public $timestamps = true;

    protected $fillable = [
        'kode_cabang',
        'kode_pemilik',
        'nama_pemilik',
        'kode_jenis_pemilik',
        'alamat1',
        'alamat2',
        'kota',
        'kode_pos',
        'po_box',
        'telepon',
        'fax',
        'handphone',
        'email',
        'tgl_lahir',
        'kode_agama',
        'no_identitas',
        'npwp',
        'file_ktp',
        'file_npwp',
        'file_sim',
        'created_by',
        'updated_by'
    ];
}
