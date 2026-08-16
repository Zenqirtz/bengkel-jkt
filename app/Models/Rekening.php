<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    protected $table = 'm_bank_rekening';

    public $timestamps = true; // set true jika ada created_at/updated_at

    protected $fillable = [
        'kode_cabang',
        'kode_bank',
        'no_rekening',
        'created_by',
        'updated_by',
    ];

}
