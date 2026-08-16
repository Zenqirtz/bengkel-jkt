<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HO extends Model
{
  protected $table = 'm_ho';

  public $timestamps = true;

  protected $fillable = [
    'ho_id',
    'ho_name',
    'no_rekening',
    'created_by',
    'updated_by'
  ];
}
