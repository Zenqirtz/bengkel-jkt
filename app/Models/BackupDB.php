<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupDB extends Model
{
    protected $table = 'backup_db';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'file_size', 
        'file_backup', 
        'created_by',
        'updated_by'
    ];

}
