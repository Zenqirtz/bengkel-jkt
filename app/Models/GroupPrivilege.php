<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupPrivilege extends Model
{
    protected $table = 'group_detail';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'groupid', 
        'menuid', 
        'isList', 
        'isAdd', 
        'isEdit', 
        'isDelete', 
        'created_by',
        'updated_by'
    ];

}
