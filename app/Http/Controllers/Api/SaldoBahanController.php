<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bahan;
use App\Models\SaldoBahan;

class SaldoBahanController extends Controller
{
    public function __invoke()
    {
        $bulan = (int) date('m');
        $tahun = (int) date('Y');
        $kode_bahan = (string) request('bahan', '');
        $kode_cabang = (string) request('cabang', '');

        $bahan = Bahan::query()
        ->where('kode_bahan', $kode_bahan)
        ->where('kode_cabang', $kode_cabang)
        ->orderBy('kode_bahan', 'asc')
        ->first();

        $items = SaldoBahan::query()
        ->where('kode_cabang', $kode_cabang)
        ->where('kode_bahan', $kode_bahan)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();

        if ($items) {
            $items->kode_satuan2 = $bahan ? $bahan->kode_satuan2 : '';
            $items->unit_akhir = number_format($items->unit_akhir ?? 0, 2, '.', ',');
            $items->harga_akhir = number_format($items->harga_akhir ?? 0, 2, '.', ',');
            $items->jumlah_akhir = number_format($items->jumlah_akhir ?? 0, 2, '.', ',');
        }

        // Kembalikan array string, sesuai script fetch Anda
        return response()->json($items, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
}
