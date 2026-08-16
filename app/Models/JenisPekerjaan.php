<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPekerjaan extends Model
{
    protected $table = 'm_jenis_pekerjaan';

    public $timestamps = true; // pastikan ini true jika ada created_at, updated_at

    protected $fillable = [
        'kode_cabang',
        'kode_jenis_pekerjaan',
        'jenis_pekerjaan',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
