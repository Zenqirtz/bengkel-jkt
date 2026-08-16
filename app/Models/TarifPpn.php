<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifPpn extends Model
{
    protected $table = 'm_tarif_ppn';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'startdate', 
        'enddate', 
        'ppn',
        'created_by',
        'updated_by'
    ];

}
