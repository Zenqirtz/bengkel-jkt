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

        $ip = request()->ip() ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $username = Auth::user()?->username ?? 'SYSTEM';

        $data = [
            'wkip' => $ip,
            'created_by' => $username,
            'updated_by' => $username,
            'description' => $description
        ];

        if(!empty($payloads)) {
            $data['payloads'] = json_encode($payloads);
        }

        self::create($data);
    }

}
