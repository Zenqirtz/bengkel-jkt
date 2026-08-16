<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosisiPekerjaan extends Model
{
    protected $table = 'm_posisi_pekerjaan';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_posisi', 
        'posisi_pekerjaan', 
        'seq_no',
        'is_active',
        'created_by',
        'updated_by'
    ];

}
