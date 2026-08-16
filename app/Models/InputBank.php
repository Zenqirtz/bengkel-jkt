<?php
// app/Models/InputBank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputBank extends Model
{
    protected $table = 't_input_bank';

    public $timestamps = true;

    protected $fillable = [
        'kode_cabang',
        'no_voucher',
        'tanggal',
        'jenis',
        'transaksi',
        // 'no_inv_single',
        'no_inv_gabung',
        'kode_bank',
        'no_rekening',
        'nilai',
        // 'jml_dibayar',
        'dp',
        'no_uang_muka',
        'pph',
        'biaya_merimen',
        'biaya_admin',
        'sisa',
        'account_coa',
        'no_spk',
        'keterangan',
        // 'status',
        'created_by',
        'updated_by',
    ];
}
