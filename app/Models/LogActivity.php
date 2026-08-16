<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogActivity extends Model
{
    protected $table = 'log_activity';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'wkip', 
        'description',
        'payloads',
        'created_by',
        'updated_by'
    ];

    public static function saveLogActivity($description, $payloads=[])
    {

        $data = [
            'wkip' => $_SERVER['REMOTE_ADDR'],
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
            'description' => $description
        ];

        if(count($payloads)) {
            $data['payloads'] = json_encode($payloads);
        }

        self::create($data);
    }

}
