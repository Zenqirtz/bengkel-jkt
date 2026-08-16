<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPemilik extends Model
{
    use HasFactory;

    protected $table = 'm_pemilik_hdr';
    // protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'kode_cabang',
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
        'created_by',
        'updated_by'
    ];
}
