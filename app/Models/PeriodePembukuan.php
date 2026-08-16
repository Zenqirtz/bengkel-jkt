<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodePembukuan extends Model
{
    protected $table = 'm_periode_pembukuan';

    // public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'tgl_periode', 
        'keterangan', 
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
}
