<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualBook extends Model
{
  protected $table = 'manual_book';

  public $timestamps = true;

  protected $fillable = [
    'nama_file',
    'file_pdf',
    'ukuran',
    'created_by',
    'updated_by',
  ];
}
