<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    protected $table = 'parameter';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode', 
        'keterangan', 
        'nama_tabel', 
        'no_urut', 
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];

}
