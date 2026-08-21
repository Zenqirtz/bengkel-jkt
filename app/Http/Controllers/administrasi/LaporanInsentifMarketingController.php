<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanInsentifMarketingExport;

use App\Helpers\Helpers as Helper;

class LaporanInsentifMarketingController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanInsentifMarketing(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Insentif Marketing';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tanggal_dari'] = date("d/m/Y");
      $datafilter['tanggal_sampai'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-insentif-marketing', [
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

    $base = DB::table('v_rep_incentive_marketing as k')
      ->where('k.kode_cabang', $user_cabang);

    $query = (clone $base);

    // Ambil tanggal dari request atau session
    $tanggalDari = $request->filled('tanggal_dari')
      ? $request->tanggal_dari
      : session('datafilter.tanggal_dari', date("d/m/Y"));

    $tanggalSampai = $request->filled('tanggal_sampai')
      ? $request->tanggal_sampai
      : session('datafilter.tanggal_sampai', date("d/m/Y"));

    // Filter berdasarkan tanggal
    if (!empty($tanggalDari) && !empty($tanggalSampai)) {
      try {
        $tglDari = Carbon::createFromFormat('d/m/Y', $tanggalDari, 'Asia/Jakarta')->format('Y-m-d');
        $tglSampai = Carbon::createFromFormat('d/m/Y', $tanggalSampai, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereBetween('k.tanggal', [$tglDari, $tglSampai]);
      } catch (\Exception $e) {
        // Handle error format tanggal
      }
    }

    $totalData = (clone $query)->count('k.kode_estimasi');
    $totalFiltered = (clone $query)->count('k.kode_estimasi');

    $datas = $query
      ->select([
        'k.tanggal',
        'k.kode_spk',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.nama_pelanggan',
        'k.kode_estimasi',
        'k.total_perbaikan',
        'k.total_sparepart',
        'k.total_lain',
        'k.ppn',
        'k.kode_claim',
        'k.kode_cabang',
        'k.nama_cabang',
        'k.nama_marketing',
      ])
      ->orderBy('k.tanggal', 'asc')
      ->get();

    $data = [];
    $fake = 0;
    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$fake,
        'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
        'kode_spk' => $row->kode_spk,
        'no_polisi' => $row->no_polisi,
        'tipe_kendaraan' => $row->tipe_kendaraan,
        'nama_pelanggan' => $row->nama_pelanggan,
        'kode_estimasi' => $row->kode_estimasi,
        'total_perbaikan' => number_format($row->total_perbaikan, 0, ',', '.'),
        'total_sparepart' => number_format($row->total_sparepart, 0, ',', '.'),
        'total_lain' => number_format($row->total_lain, 0, ',', '.'),
        'ppn' => number_format($row->ppn, 0, ',', '.'),
        'kode_claim' => $row->kode_claim,
        'total_estimasi' => number_format($row->total_perbaikan + $row->total_sparepart + $row->total_lain + $row->ppn, 0, ',', '.'),
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
        'tanggal_dari' => 'required',
        'tanggal_sampai' => 'required'
      ],
      [
        'tanggal_dari.required' => 'Tanggal Dari wajib diisi.',
        'tanggal_sampai.required' => 'Tanggal Sampai wajib diisi.',
      ]
    );

    $dataArray['tanggal_dari'] = $request->tanggal_dari;
    $dataArray['tanggal_sampai'] = $request->tanggal_sampai;

    return redirect('administrasi/laporan-insentif-marketing')->with('datafilter', $dataArray);
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

    // Format Periode
    $tglDari = $filters['tanggal_dari'] ?? date('d/m/Y');
    $tglSampai = $filters['tanggal_sampai'] ?? date('d/m/Y');
    $periodeStr = $tglDari . ' s/d ' . $tglSampai;

    // Ambil nama marketing dari filter atau data pertama
    $namaMarketing = $filters['nama_marketing'] ?? '-';

    // Jika tidak ada filter nama marketing, ambil dari data pertama
    if ($namaMarketing == '-') {
      $firstData = DB::table('v_rep_incentive_marketing as k')
        ->where('k.kode_cabang', $user_cabang);

      if (!empty($filters['tanggal_dari']) && !empty($filters['tanggal_sampai'])) {
        try {
          $tglDari = Carbon::createFromFormat('d/m/Y', $filters['tanggal_dari'])->format('Y-m-d');
          $tglSampai = Carbon::createFromFormat('d/m/Y', $filters['tanggal_sampai'])->format('Y-m-d');
          $firstData->whereBetween('k.tanggal', [$tglDari, $tglSampai]);
        } catch (\Exception $e) {
        }
      }

      $result = $firstData->first();
      $namaMarketing = $result->nama_marketing ?? '-';
    }

    $fileName = 'Laporan_Insentif_Marketing_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanInsentifMarketingExport($filters, $cabangData, $periodeStr, $namaMarketing), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Insentif Marketing';

    $filters = $request->all();

    // Format Periode
    $tglDari = $filters['tanggal_dari'] ?? date('d/m/Y');
    $tglSampai = $filters['tanggal_sampai'] ?? date('d/m/Y');
    $periodeStr = $tglDari . ' s/d ' . $tglSampai;

    // Data
    $query = DB::table('v_rep_incentive_marketing as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.tanggal',
        'k.kode_spk',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.nama_pelanggan',
        'k.kode_estimasi',
        'k.total_perbaikan',
        'k.total_sparepart',
        'k.total_lain',
        'k.ppn',
        'k.kode_claim',
        'k.nama_marketing',
      ])
      ->orderBy('k.tanggal', 'asc');

    // Filtering berdasarkan tanggal
    if (!empty($filters['tanggal_dari']) && !empty($filters['tanggal_sampai'])) {
      try {
        $tanggalDari = Carbon::createFromFormat('d/m/Y', $filters['tanggal_dari'])->format('Y-m-d');
        $tanggalSampai = Carbon::createFromFormat('d/m/Y', $filters['tanggal_sampai'])->format('Y-m-d');
        $query->whereBetween('k.tanggal', [$tanggalDari, $tanggalSampai]);
      } catch (\Exception $e) {
      }
    }

    $datas = $query->get();

    // Ambil nama marketing dari filter atau data pertama
    $namaMarketing = $filters['nama_marketing'] ?? ($datas->first()->nama_marketing ?? '-');

    $pages = 'content.administrasi.laporan.laporan-insentif-marketing-print';

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'namaMarketing' => $namaMarketing,
      'no' => 1,
      'datas' => $datas,
      'datafilter' => $filters,
      'pageConfigs' => $pageConfigs,
    ]);
  }
}
