<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPrivilege extends Model
{
    protected $table = 'users_group';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'userid', 
        'groupid', 
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];

}
