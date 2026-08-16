<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Pengumuman extends Model
{
    protected $table = 'm_pengumuman';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'startdate', 
        'enddate', 
        'notes',
        // 'created_at',
        // 'updated_at',
        'created_by',
        'updated_by'
    ];

    // protected $casts = [
    //     'startdate' => 'date',
    //     'enddate'   => 'date',
    // ];

    // Scope pengumuman yang aktif hari ini (WIB)
    public function scopeAktifHariIni($query)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        return $query->whereDate('startdate', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('enddate')->orWhereDate('enddate', '>=', $today);
                });
    }
}
