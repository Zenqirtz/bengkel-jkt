<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKontrolPemakaianBahanExport;
use App\Models\LogActivity;

use App\Helpers\Helpers as Helper;

class LaporanKontrolPemakaianBahanController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanKontrolPemakaianBahan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Laporan Kontrol Pemakaian Bahan';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_kontrol_pemakaian_bahan');
    if (empty($datafilter)) {
      $datafilter['no_spk'] = '';
    }

    ## Log Activity
    $desc = "View Laporan " . $title;
    LogActivity::saveLogActivity($desc);

    return view('content.gudang.laporan.laporan-kontrol-pemakaian-bahan', [
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

    $no_spk = $request->filled('no_spk')
      ? $request->no_spk
      : session('datafilter_kontrol_pemakaian_bahan.no_spk', '');

    if (empty($no_spk)) {
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'meta' => null,
      ]);
    }

    // Call stored procedure
    $results = DB::select('CALL up_apl_rep_kontrol_pemakaian_bahan(?, ?)', [
      $user_cabang,
      $no_spk,
    ]);

    $meta = null;
    $data = [];
    $no = 0;

    foreach ($results as $row) {
      // Capture meta from first row
      if ($meta === null) {
        $meta = [
          'no_spk' => $no_spk,
          'point_panel' => $row->point_panel ?? '',
          'nama_pemilik' => $row->nama_pemilik ?? '',
          'merek_tipe' => $row->merek_tipe ?? '',
          'nama_cabang' => $row->nama_cabang ?? '',
        ];
      }

      $data[] = [
        'no' => ++$no,
        'posisi_pekerjaan' => $row->posisi_pekerjaan ?? '',
        'nama_bahan' => $row->nama_bahan ?? '',
        'std_qty' => number_format($row->qty ?? 0, 2, '.', '.'),
        'std_harga' => number_format($row->harga ?? 0, 0, '.', '.'),
        'aktual_qty' => number_format($row->qty_actual ?? 0, 2, '.', '.'),
        'aktual_harga' => number_format($row->harga_actual ?? 0, 0, '.', '.'),
        'aktual_total' => number_format($row->tot_harga_actual ?? 0, 0, '.', '.'),
        'variance_qty' => number_format($row->qty_variance ?? 0, 2, '.', '.'),
        'variance_harga' => number_format($row->tot_harga_variance ?? 0, 0, '.', '.'),
      ];
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => count($data),
      'recordsFiltered' => count($data),
      'data' => $data,
      'meta' => $meta,
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
    $request->validate(
      ['no_spk' => 'required'],
      ['no_spk.required' => 'Nomor SPK wajib diisi.']
    );

    $dataArray['no_spk'] = $request->no_spk;

    ## Log Activity
    LogActivity::saveLogActivity('Filter Laporan Kontrol Pemakaian Bahan', $dataArray);

    return redirect('gudang/laporan-kontrol-pemakaian-bahan')
      ->with('datafilter_kontrol_pemakaian_bahan', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $no_spk = $request->input('no_spk', '');

    if (empty($no_spk)) {
      return back()->with('error', 'Nomor SPK wajib diisi untuk export.');
    }

    $cabangData = [
      'kode' => $user_cabang,
      'nama' => $namaCabang,
    ];

    $fileName = 'Laporan_Kontrol_Pemakaian_Bahan_' . date('Ymd_His') . '.xlsx';

    ## Log Activity
    LogActivity::saveLogActivity('Export Excel Laporan Kontrol Pemakaian Bahan', ['no_spk' => $no_spk]);

    return Excel::download(
      new LaporanKontrolPemakaianBahanExport($no_spk, $cabangData),
      $fileName
    );
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $no_spk = $request->input('no_spk', '');

    $title = 'Laporan Kontrol Pemakaian Bahan';

    $results = [];
    $meta = null;

    if (!empty($no_spk)) {
      $results = DB::select('CALL up_apl_rep_kontrol_pemakaian_bahan(?, ?)', [
        $user_cabang,
        $no_spk,
      ]);

      foreach ($results as $row) {
        if ($meta === null) {
          $meta = [
            'no_spk' => $no_spk,
            'point_panel' => $row->point_panel ?? '',
            'nama_pemilik' => $row->nama_pemilik ?? '',
            'merek_tipe' => $row->merek_tipe ?? '',
            'nama_cabang' => $row->nama_cabang ?? '',
          ];
        }
      }
    }

    $pageConfigs = ['myLayout' => 'blank'];

    ## Log Activity
    LogActivity::saveLogActivity('Print Laporan Kontrol Pemakaian Bahan', ['no_spk' => $no_spk]);

    return view('content.gudang.laporan.laporan-kontrol-pemakaian-bahan-print', [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'no_spk' => $no_spk,
      'meta' => $meta,
      'datas' => $results,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
