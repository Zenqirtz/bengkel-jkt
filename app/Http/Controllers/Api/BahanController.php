<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bahan;

class BahanController extends Controller
{
    public function __invoke()
    {
        $kode_bahan = (string) request('bahan', '');
        $kode_cabang = (string) request('cabang', '');

        $items = Bahan::query()
        ->where('kode_bahan', $kode_bahan)
        ->where('kode_cabang', $kode_cabang)
        ->orderBy('kode_bahan', 'asc')
        ->first();

        // Kembalikan array string, sesuai script fetch Anda
        return response()->json($items, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
}
