<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRekapPointPanelExport;

use App\Helpers\Helpers as Helper;

class LaporanRekapPointPanelController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanRekapPointPanel(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Rekap Point Panel';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tahun'] = '2025';
    }

    return view('content.administrasi.laporan.laporan-rekap-point-panel', [
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

    // Query data tanpa filter tahun - ambil semua data dari view
    $query = DB::table('v_rep_point_panel as k')
      ->where('k.kode_cabang', $user_cabang);

    // Total data
    $totalData = (clone $query)->count();
    $totalFiltered = (clone $query)->count();

    // Ambil data
    $datas = $query
      ->select([
        'k.kode_cabang',
        'k.nama_cabang',
        'k.bulan',
        'k.jumlah_spk',
        'k.total_panel',
      ])
      ->orderBy('k.bulan', 'asc')
      ->get();

    // Susun data per bulan
    $data = [];
    $no = 0;
    $grandJumlahSpk = 0;
    $grandTotalPanel = 0;

    // Array untuk mapping bulan
    $bulanNama = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember'
    ];

    foreach ($datas as $row) {
      // bulan adalah integer 1-12
      $bulanAngka = (int) $row->bulan;

      $data[] = [
        'no' => ++$no,
        'bulan' => $bulanNama[$bulanAngka] ?? '',
        'jumlah_spk' => number_format($row->jumlah_spk, 0, ',', '.'),
        'total_panel' => number_format($row->total_panel, 2, ',', '.'),
      ];

      $grandJumlahSpk += $row->jumlah_spk;
      $grandTotalPanel += $row->total_panel;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_jumlah_spk' => number_format($grandJumlahSpk, 0, ',', '.'),
      'grand_total_panel' => number_format($grandTotalPanel, 2, ',', '.'),
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
        'tahun' => 'required'
      ],
      [
        'tahun.required' => 'Tahun wajib diisi.'
      ]
    );

    $dataArray['tahun'] = $request->tahun;

    return redirect('administrasi/laporan-rekap-point-panel')->with('datafilter', $dataArray);
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
    $tahun = $request->input('tahun', session('datafilter.tahun', date('Y')));

    $fileName = 'Laporan_Rekap_Point_Panel_' . $tahun . '_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanRekapPointPanelExport($filters, $cabangData, $tahun), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Rekap Point Panel';
    $filters = $request->all();

    $tahun = $request->input('tahun', session('datafilter.tahun', date('Y')));

    // Query data tanpa filter tahun
    $query = DB::table('v_rep_point_panel as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_cabang',
        'k.nama_cabang',
        'k.bulan',
        'k.jumlah_spk',
        'k.total_panel',
      ])
      ->orderBy('k.bulan', 'asc');

    $datas = $query->get();

    $pages = 'content.administrasi.laporan.laporan-rekap-point-panel-print';

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'tahun' => $tahun,
      'no' => 1,
      'datas' => $datas,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
