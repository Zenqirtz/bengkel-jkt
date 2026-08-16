<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'group';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'nama', 
        'keterangan', 
        'active', 
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];

}
