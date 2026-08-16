<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'parent_id', 
        'custom_title', 
        'url_menu', 
        'path_icon',
        'slug',
        'tid',
        'active',
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];

}
