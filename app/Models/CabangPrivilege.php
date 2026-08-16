<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabangPrivilege extends Model
{
    protected $table = 'users_cabang';

    // public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'userid', 
        'cabangid', 
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];

}
