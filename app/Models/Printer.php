<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $table = 'm_printer';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'printer', 
        'kertas', 
        'font', 
        'paragraph', 
        'created_at',
        'updated_at',
        'created_by',
        'updated_by'
    ];
}
