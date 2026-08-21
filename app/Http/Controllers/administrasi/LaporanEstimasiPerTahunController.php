<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanEstimasiPerTahunExport;

use App\Helpers\Helpers as Helper;

class LaporanEstimasiPerTahunController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanEstimasiPerTahun(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Estimasi Per Tahun';

    $user_cabang = session('kd_cabang');
    $months = Helper::listMonths();
    $years = Helper::listYears();

    $jenis_laporan = [
      'bulan' => 'Laporan Rekap Per Tipe'
    ];

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['jenis_laporan'] = 'bulan';
      $datafilter['tahun'] = date("Y");
      $datafilter['bulan'] = date("m");
    }

    return view('content.administrasi.laporan.laporan-estimasi-per-tahun', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'months' => $months,
      'years' => $years,
      'jenis_laporan' => $jenis_laporan,
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

    // Base query
    $query = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $user_cabang);

    // Filter berdasarkan jenis laporan
    if ($request->filled('tahun') && $request->filled('bulan')) {
      $query->whereMonth('k.tanggal', $request->bulan);
      $query->whereYear('k.tanggal', $request->tahun);
    }

    // Group by nama_pelanggan dan aggregate
    $datas = $query
      ->select([
        'k.nama_pelanggan',
        DB::raw('COUNT(DISTINCT k.kode_spk) as unit'),
        DB::raw('SUM(k.perbaikan_r) as perbaikan_r'),
        DB::raw('SUM(k.perbaikan_s) as perbaikan_s'),
        DB::raw('SUM(k.perbaikan_t) as perbaikan_t'),
        DB::raw('SUM(k.total_perbaikan) as total_perbaikan'),
        DB::raw('SUM(k.sparepart_r) as sparepart_r'),
        DB::raw('SUM(k.sparepart_s) as sparepart_s'),
        DB::raw('SUM(k.sparepart_t) as sparepart_t'),
        DB::raw('SUM(k.total_sparepart) as total_sparepart'),
        DB::raw('SUM(k.lain_r) as lain_r'),
        DB::raw('SUM(k.lain_s) as lain_s'),
        DB::raw('SUM(k.lain_t) as lain_t'),
        DB::raw('SUM(k.total_lain) as total_lain'),
        DB::raw('SUM(k.perbaikan_r + k.sparepart_r + k.lain_r) as total_r'),
        DB::raw('SUM(k.perbaikan_s + k.sparepart_s + k.lain_s) as total_s'),
        DB::raw('SUM(k.perbaikan_t + k.sparepart_t + k.lain_t) as total_t'),
        DB::raw('SUM(k.ppn) as ppn'),
        DB::raw('SUM(k.total) as total')
      ])
      ->groupBy('k.nama_pelanggan')
      ->orderBy('k.nama_pelanggan', 'asc')
      ->get();

    $totalData = $datas->count();
    $totalFiltered = $datas->count();

    // Hitung Grand Total
    $grandUnit = 0;
    $grandPerbaikanR = 0;
    $grandPerbaikanS = 0;
    $grandPerbaikanT = 0;
    $grandTotalPerbaikan = 0;
    $grandSparepartR = 0;
    $grandSparepartS = 0;
    $grandSparepartT = 0;
    $grandTotalSparepart = 0;
    $grandLainR = 0;
    $grandLainS = 0;
    $grandLainT = 0;
    $grandTotalLain = 0;
    $grandTotalR = 0;
    $grandTotalS = 0;
    $grandTotalT = 0;
    $grandPPN = 0;
    $grandTotal = 0;

    // Format data
    $data = [];
    $no = 1;
    foreach ($datas as $row) {
      $data[] = [
        'no' => $no++,
        'nama_pelanggan' => $row->nama_pelanggan,
        'unit' => $row->unit,
        'perbaikan_r' => number_format($row->perbaikan_r, 0, ',', '.'),
        'perbaikan_s' => number_format($row->perbaikan_s, 0, ',', '.'),
        'perbaikan_t' => number_format($row->perbaikan_t, 0, ',', '.'),
        'total_perbaikan' => number_format($row->total_perbaikan, 0, ',', '.'),
        'sparepart_r' => number_format($row->sparepart_r, 0, ',', '.'),
        'sparepart_s' => number_format($row->sparepart_s, 0, ',', '.'),
        'sparepart_t' => number_format($row->sparepart_t, 0, ',', '.'),
        'total_sparepart' => number_format($row->total_sparepart, 0, ',', '.'),
        'lain_r' => number_format($row->lain_r, 0, ',', '.'),
        'lain_s' => number_format($row->lain_s, 0, ',', '.'),
        'lain_t' => number_format($row->lain_t, 0, ',', '.'),
        'total_lain' => number_format($row->total_lain, 0, ',', '.'),
        'total_r' => number_format($row->total_r, 0, ',', '.'),
        'total_s' => number_format($row->total_s, 0, ',', '.'),
        'total_t' => number_format($row->total_t, 0, ',', '.'),
        'ppn' => number_format($row->ppn, 0, ',', '.'),
        'total' => number_format($row->total, 0, ',', '.'),
      ];

      // Akumulasi grand total
      $grandUnit += $row->unit;
      $grandPerbaikanR += $row->perbaikan_r;
      $grandPerbaikanS += $row->perbaikan_s;
      $grandPerbaikanT += $row->perbaikan_t;
      $grandTotalPerbaikan += $row->total_perbaikan;
      $grandSparepartR += $row->sparepart_r;
      $grandSparepartS += $row->sparepart_s;
      $grandSparepartT += $row->sparepart_t;
      $grandTotalSparepart += $row->total_sparepart;
      $grandLainR += $row->lain_r;
      $grandLainS += $row->lain_s;
      $grandLainT += $row->lain_t;
      $grandTotalLain += $row->total_lain;
      $grandTotalR += $row->total_r;
      $grandTotalS += $row->total_s;
      $grandTotalT += $row->total_t;
      $grandPPN += $row->ppn;
      $grandTotal += $row->total;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_unit' => $grandUnit,
      'grand_perbaikan_r' => number_format($grandPerbaikanR, 0, ',', '.'),
      'grand_perbaikan_s' => number_format($grandPerbaikanS, 0, ',', '.'),
      'grand_perbaikan_t' => number_format($grandPerbaikanT, 0, ',', '.'),
      'grand_total_perbaikan' => number_format($grandTotalPerbaikan, 0, ',', '.'),
      'grand_sparepart_r' => number_format($grandSparepartR, 0, ',', '.'),
      'grand_sparepart_s' => number_format($grandSparepartS, 0, ',', '.'),
      'grand_sparepart_t' => number_format($grandSparepartT, 0, ',', '.'),
      'grand_total_sparepart' => number_format($grandTotalSparepart, 0, ',', '.'),
      'grand_lain_r' => number_format($grandLainR, 0, ',', '.'),
      'grand_lain_s' => number_format($grandLainS, 0, ',', '.'),
      'grand_lain_t' => number_format($grandLainT, 0, ',', '.'),
      'grand_total_lain' => number_format($grandTotalLain, 0, ',', '.'),
      'grand_total_r' => number_format($grandTotalR, 0, ',', '.'),
      'grand_total_s' => number_format($grandTotalS, 0, ',', '.'),
      'grand_total_t' => number_format($grandTotalT, 0, ',', '.'),
      'grand_ppn' => number_format($grandPPN, 0, ',', '.'),
      'grand_total' => number_format($grandTotal, 0, ',', '.'),
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
        'jenis_laporan' => 'required',
        'tahun' => 'required',
        'bulan' => 'required'
      ],
      [
        'jenis_laporan.required' => 'Jenis Laporan wajib diisi.',
        'tahun.required' => 'Tahun wajib diisi.',
        'bulan.required' => 'Bulan wajib diisi.'
      ]
    );

    $dataArray['jenis_laporan'] = $request->jenis_laporan;
    $dataArray['tahun'] = $request->tahun;
    $dataArray['bulan'] = $request->bulan;

    return redirect('administrasi/laporan-estimasi-per-tahun')->with('datafilter', $dataArray);
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

    // Format String Periode
    $months = Helper::listMonths();
    $periodeStr = $months[$request->bulan] . ' ' . $request->tahun;

    $fileName = 'Laporan_Estimasi_Per_Tahun_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanEstimasiPerTahunExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Rekap Estimasi Per Tipe';

    $filters = $request->all();

    // Format String Periode
    $months = Helper::listMonths();
    $periodeStr = $months[$filters['bulan']] . ' ' . $filters['tahun'];

    // Query data
    $query = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $user_cabang);

    if (!empty($filters['tahun']) && !empty($filters['bulan'])) {
      $query->whereMonth('k.tanggal', $filters['bulan']);
      $query->whereYear('k.tanggal', $filters['tahun']);
    }

    $datas = $query
      ->select([
        'k.nama_pelanggan',
        DB::raw('COUNT(DISTINCT k.kode_spk) as unit'),
        DB::raw('SUM(k.perbaikan_r) as perbaikan_r'),
        DB::raw('SUM(k.perbaikan_s) as perbaikan_s'),
        DB::raw('SUM(k.perbaikan_t) as perbaikan_t'),
        DB::raw('SUM(k.total_perbaikan) as total_perbaikan'),
        DB::raw('SUM(k.sparepart_r) as sparepart_r'),
        DB::raw('SUM(k.sparepart_s) as sparepart_s'),
        DB::raw('SUM(k.sparepart_t) as sparepart_t'),
        DB::raw('SUM(k.total_sparepart) as total_sparepart'),
        DB::raw('SUM(k.lain_r) as lain_r'),
        DB::raw('SUM(k.lain_s) as lain_s'),
        DB::raw('SUM(k.lain_t) as lain_t'),
        DB::raw('SUM(k.total_lain) as total_lain'),
        DB::raw('SUM(k.perbaikan_r + k.sparepart_r + k.lain_r) as total_r'),
        DB::raw('SUM(k.perbaikan_s + k.sparepart_s + k.lain_s) as total_s'),
        DB::raw('SUM(k.perbaikan_t + k.sparepart_t + k.lain_t) as total_t'),
        DB::raw('SUM(k.ppn) as ppn'),
        DB::raw('SUM(k.total) as total')
      ])
      ->groupBy('k.nama_pelanggan')
      ->orderBy('k.nama_pelanggan', 'asc')
      ->get();

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.administrasi.laporan.laporan-estimasi-per-tahun-print', [
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
