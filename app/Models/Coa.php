<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $table = 'm_coa';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'acct_cd', 
        'descs', 
        'class_cd',
        'ilevel',
        'seq_no',
        'active_status',
        'acct_type',
        'created_by',
        'updated_by'
    ];

}
