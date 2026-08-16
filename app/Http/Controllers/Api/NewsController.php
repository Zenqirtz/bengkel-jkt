<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;

class NewsController extends Controller
{
    /**
     * GET /api/news
     * Optional query: ?limit=10  (default 20)
     *                  ?include_inactive=1  (untuk ambil semua, bukan hanya yang aktif hari ini)
     * Response: ["teks 1", "teks 2", ...]
     */
    public function __invoke()
    {
        $limit = (int) request('limit', 20);
        $includeInactive = (bool) request('include_inactive', false);

        $query = Pengumuman::query()
            ->when(!$includeInactive, fn ($q) => $q->aktifHariIni())
            ->orderBy('startdate', 'desc')
            ->orderBy('enddate', 'desc');

        // Ambil hanya kolom notes
        $items = $query->limit($limit)->pluck('notes')->filter()->values();

        // Kembalikan array string, sesuai script fetch Anda
        return response()->json($items, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
}
