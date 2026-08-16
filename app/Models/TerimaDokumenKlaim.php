<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerimaDokumenKlaim extends Model
{
  protected $table = 't_dokumen_checklist';

  public $timestamps = true;

  protected $fillable = [
    'kode_cabang',
    'kode_spk',
    'doc_seq_no',
    'tgl_dokumen',
    'doc_desc',
    'isi_dokumen',
    'checklist',
    'created_by',
    'updated_by'
  ];
}
