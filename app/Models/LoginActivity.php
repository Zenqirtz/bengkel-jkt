<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    protected $table = 'login_activity';

    public $timestamps = false; // set true jika ada created_at/updated_at

    protected $fillable = [
        'userid', 
        'session_wkid', 
        'session_wkip',
        'session_startlogin',
        'session_endlogin',
        'session_closeby',
        'session_loginflag'
    ];

}
