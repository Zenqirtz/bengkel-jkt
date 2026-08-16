<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\ProfilePerusahaan;

class CabangController extends Controller
{
    /**
     * GET /api/cabangs
     */
    public function __invoke()
    {
        $userId = Auth::user()->id;

        $q = trim((string) request('q', ''));

        $query = ProfilePerusahaan::from('m_cabang as a')
            ->join('v_cabang_akses as b', 'b.cabangid', '=', 'a.id')
            ->select('a.*')
            ->where('b.userid', $userId)
            ->when($q !== '' && $q !== null, function ($qr) use ($q) {
                $qr->where('a.nama_cabang', 'like', "%{$q}%");
            })
            ->orderBy('a.nourut', 'asc')
            ->orderBy('a.nama_cabang', 'asc');   // (opsional)

        $rows = $query->get(['id', 'nama_cabang']);

        // mapping ke { name, icon, url }
        $items = $rows->map(function ($r) {
            return [
                'name' => $r->nama_cabang,
                'icon' => 'ri-home-7-line',
                'url'  => 'change-cabang/' . $r->id,
            ];
        })->values();

        // sesuai struktur cabang.json: navigation & suggestions punya grup "Cabang"
        $payload = [
            'navigation'  => [ 'Cabang' => $items ],
            'suggestions' => [ 'Cabang' => $items ],
        ];

        return response()->json($payload, 200, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }
}
