<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanEstimasiBelumDikirimExport;

use App\Helpers\Helpers as Helper;

class LaporanEstimasiBelumDikirimController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanEstimasiBelumDikirim(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Estimasi Belum Dikirim';

    $user_cabang = session('kd_cabang');

    // Ubah session key menjadi 'datafilter' saja
    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tanggal'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-estimasi-belum-dikirim', [
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

    $base = DB::table('v_rep_estimasi_belum_dikirim as k')
      ->where('k.kode_cabang', $user_cabang);

    $query = (clone $base);

    // Ambil tanggal dari request atau session
    $tanggal = $request->filled('tanggal')
      ? $request->tanggal
      : session('datafilter.tanggal', date("d/m/Y"));

    // Filter berdasarkan tanggal masuk
    if (!empty($tanggal)) {
      try {
        $tanggalFormat = Carbon::createFromFormat('d/m/Y', $tanggal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $tanggalFormat);
      } catch (\Exception $e) {
        // Handle error format tanggal
      }
    }

    $totalData = (clone $query)->count('k.kode_spk');
    $totalFiltered = (clone $query)->count('k.kode_spk');

    $datas = $query
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.nama_pelanggan',
        'k.kode_estimasi',
        'k.tanggal',
      ])
      ->orderBy('k.tgl_masuk', 'asc')
      ->get();

    $data = [];
    $fake = 0;
    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$fake,
        'kode_spk' => $row->kode_spk,
        'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
        'no_polisi' => $row->no_polisi,
        'tipe_kendaraan' => $row->tipe_kendaraan,
        'nama_pelanggan' => $row->nama_pelanggan,
        'kode_estimasi' => $row->kode_estimasi,
      ];
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
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
        'tanggal' => 'required'
      ],
      [
        'tanggal.required' => 'Tanggal wajib diisi.',
      ]
    );

    $dataArray['tanggal'] = $request->tanggal;

    // Ubah session key menjadi 'datafilter' saja
    return redirect('administrasi/laporan-estimasi-belum-dikirim')->with('datafilter', $dataArray);
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

    // Format Periode - hanya tanggal
    $tglFilter = $request->input('tanggal', date('d/m/Y'));
    $periodeStr = $tglFilter;

    $fileName = 'Laporan_Estimasi_Belum_Dikirim_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanEstimasiBelumDikirimExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */

  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Estimasi Belum Dikirim';

    $filters = $request->all();

    // Format Periode - hanya tanggal
    $tglFilter = $filters['tanggal'] ?? date('d/m/Y');
    $periodeStr = $tglFilter;

    // Data
    $query = DB::table('v_rep_estimasi_belum_dikirim as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.nama_pelanggan',
        'k.kode_estimasi',
        'k.tanggal',
      ])
      ->orderBy('k.tgl_masuk', 'asc');

    // Filtering berdasarkan tanggal masuk
    if (!empty($filters['tanggal'])) {
      try {
        $tanggal = Carbon::createFromFormat('d/m/Y', $filters['tanggal'])->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $tanggal);
      } catch (\Exception $e) {
      }
    }

    $datas = $query->get();
    $pages = 'content.administrasi.laporan.laporan-estimasi-belum-dikirim-print';

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
