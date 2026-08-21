<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanInsentifSTExport;

use App\Helpers\Helpers as Helper;

class LaporanInsentifSTController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanInsentifST(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Insentif ST';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-insentif-st', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'datafilter' => $datafilter,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    $query = DB::table('v_rep_insentif_all as k')
      ->where('k.kode_cabang', $user_cabang);

    // Ambil tanggal dari request atau session
    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter.tgl_akhir', date("d/m/Y"));

    // Filter berdasarkan tanggal estimasi
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_estimasi', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_estimasi', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    // Total data
    $totalData = (clone $query)->count();
    $totalFiltered = (clone $query)->count();

    // Ambil data
    $datas = $query
      ->select([
        'k.kode_spk',
        'k.no_polisi',
        'k.kode_estimasi',
        'k.nama_asuransi',
        'k.tgl_estimasi',
        'k.insentif_s',
        'k.insentif_t',
        'k.wm_s',
        'k.marketing_s',
        'k.kabeng_s',
        'k.sa_s',
        'k.wm_t',
        'k.marketing_t',
        'k.kabeng_t',
        'k.sa_t',
      ])
      ->orderBy('k.tgl_estimasi', 'asc')
      ->get();

    // Susun data untuk DataTables
    $data = [];
    $no = 0;
    $grandInsentifS = 0;
    $grandInsentifT = 0;
    $grandWmS = 0;
    $grandMarketingS = 0;
    $grandKabengS = 0;
    $grandSaS = 0;
    $grandWmT = 0;
    $grandMarketingT = 0;
    $grandKabengT = 0;
    $grandSaT = 0;

    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$no,
        'kode_spk' => $row->kode_spk,
        'no_polisi' => $row->no_polisi,
        'kode_estimasi' => $row->kode_estimasi,
        'nama_asuransi' => $row->nama_asuransi,
        'tgl_estimasi' => blank($row->tgl_estimasi) ? '' : date("d/m/Y", strtotime($row->tgl_estimasi)),
        'insentif_s' => number_format($row->insentif_s, 0, ',', '.'),
        'insentif_t' => number_format($row->insentif_t, 0, ',', '.'),
        'wm_s' => number_format($row->wm_s, 0, ',', '.'),
        'marketing_s' => number_format($row->marketing_s, 0, ',', '.'),
        'kabeng_s' => number_format($row->kabeng_s, 0, ',', '.'),
        'sa_s' => number_format($row->sa_s, 0, ',', '.'),
        'wm_t' => number_format($row->wm_t, 0, ',', '.'),
        'marketing_t' => number_format($row->marketing_t, 0, ',', '.'),
        'kabeng_t' => number_format($row->kabeng_t, 0, ',', '.'),
        'sa_t' => number_format($row->sa_t, 0, ',', '.'),
      ];

      $grandInsentifS += $row->insentif_s;
      $grandInsentifT += $row->insentif_t;
      $grandWmS += $row->wm_s;
      $grandMarketingS += $row->marketing_s;
      $grandKabengS += $row->kabeng_s;
      $grandSaS += $row->sa_s;
      $grandWmT += $row->wm_t;
      $grandMarketingT += $row->marketing_t;
      $grandKabengT += $row->kabeng_t;
      $grandSaT += $row->sa_t;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_insentif_s' => number_format($grandInsentifS, 0, ',', '.'),
      'grand_insentif_t' => number_format($grandInsentifT, 0, ',', '.'),
      'grand_wm_s' => number_format($grandWmS, 0, ',', '.'),
      'grand_marketing_s' => number_format($grandMarketingS, 0, ',', '.'),
      'grand_kabeng_s' => number_format($grandKabengS, 0, ',', '.'),
      'grand_sa_s' => number_format($grandSaS, 0, ',', '.'),
      'grand_wm_t' => number_format($grandWmT, 0, ',', '.'),
      'grand_marketing_t' => number_format($grandMarketingT, 0, ',', '.'),
      'grand_kabeng_t' => number_format($grandKabengT, 0, ',', '.'),
      'grand_sa_t' => number_format($grandSaT, 0, ',', '.'),
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validatedData = $request->validate(
      [
        'tgl_awal' => 'required',
        'tgl_akhir' => 'required'
      ],
      [
        'tgl_awal.required' => 'Tanggal Awal wajib diisi.',
        'tgl_akhir.required' => 'Tanggal Akhir wajib diisi.'
      ]
    );

    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;

    return redirect('administrasi/laporan-insentif-st')->with('datafilter', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang
    ];

    $filters = $request->all();
    $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
    $tglAkhir = $request->input('tgl_akhir', date('d/m/Y'));
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    $fileName = 'Laporan_Insentif_ST_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanInsentifSTExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Insentif S & T';
    $filters = $request->all();

    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rep_insentif_all as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_spk',
        'k.no_polisi',
        'k.kode_estimasi',
        'k.nama_asuransi',
        'k.tgl_estimasi',
        'k.insentif_s',
        'k.insentif_t',
        'k.wm_s',
        'k.marketing_s',
        'k.kabeng_s',
        'k.sa_s',
        'k.wm_t',
        'k.marketing_t',
        'k.kabeng_t',
        'k.sa_t',
      ]);

    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('k.tgl_estimasi', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('k.tgl_estimasi', '<=', $endDate);
    }

    $datas = $query->orderBy('k.tgl_estimasi', 'asc')->get();

    $pages = 'content.administrasi.laporan.laporan-insentif-st-print';

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'no' => 1,
      'datas' => $datas,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
