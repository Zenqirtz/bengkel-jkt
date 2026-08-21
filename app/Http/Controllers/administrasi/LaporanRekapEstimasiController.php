<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRekapEstimasiExport;

use App\Helpers\Helpers as Helper;

class LaporanRekapEstimasiController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanRekapEstimasi(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Rekap Estimasi';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-rekap-estimasi', [
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

    $query = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $user_cabang);

    // Ambil tanggal dari request atau session
    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter.tgl_akhir', date("d/m/Y"));

    // Filter berdasarkan tanggal
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tanggal', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tanggal', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    // Total data
    // $totalData = (clone $query)->count();
    // $totalFiltered = (clone $query)->count();

    // Ambil data
    $datas = $query
      ->select([
        'k.nama_pelanggan',
        DB::raw('COUNT(1) as unit'),
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
        DB::raw('SUM(k.ppn) as ppn'),
        DB::raw('SUM(k.total) as total'),
      ])
      ->groupBy('k.nama_pelanggan')
      ->orderBy('k.nama_pelanggan', 'asc')
      ->get();

    // Total data
    $totalData = count($datas);
    $totalFiltered = count($datas);

    // Susun data untuk DataTables
    $data = [];
    $no = 0;
    $grandUnit = 0;
    $grandTotal = 0;
    $grandPerbaikanR = 0;
    $grandPerbaikanS = 0;
    $grandPerbaikanT = 0;
    $grandSparepartR = 0;
    $grandSparepartS = 0;
    $grandSparepartT = 0;
    $grandLainR = 0;
    $grandLainS = 0;
    $grandLainT = 0;
    $grandPPN = 0;

    foreach ($datas as $row) {
      $totalRow = $row->total_perbaikan + $row->total_sparepart + $row->total_lain;

      $data[] = [
        'no' => ++$no,
        'nama_pelanggan' => $row->nama_pelanggan,
        'unit' => $row->unit,
        'perbaikan_r' => number_format($row->perbaikan_r, 0, '.', ','),
        'perbaikan_s' => number_format($row->perbaikan_s, 0, '.', ','),
        'perbaikan_t' => number_format($row->perbaikan_t, 0, '.', ','),
        'sparepart_r' => number_format($row->sparepart_r, 0, '.', ','),
        'sparepart_s' => number_format($row->sparepart_s, 0, '.', ','),
        'sparepart_t' => number_format($row->sparepart_t, 0, '.', ','),
        'lain_r' => number_format($row->lain_r, 0, '.', ','),
        'lain_s' => number_format($row->lain_s, 0, '.', ','),
        'lain_t' => number_format($row->lain_t, 0, '.', ','),
        'ppn' => number_format($row->ppn, 0, '.', ','),
        'total' => number_format($row->total, 0, '.', ','),
      ];

      $grandUnit += $row->unit;
      $grandTotal += $row->total;
      $grandPerbaikanR += $row->perbaikan_r;
      $grandPerbaikanS += $row->perbaikan_s;
      $grandPerbaikanT += $row->perbaikan_t;
      $grandSparepartR += $row->sparepart_r;
      $grandSparepartS += $row->sparepart_s;
      $grandSparepartT += $row->sparepart_t;
      $grandLainR += $row->lain_r;
      $grandLainS += $row->lain_s;
      $grandLainT += $row->lain_t;
      $grandPPN += $row->ppn;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_unit' => $grandUnit,
      'grand_total' => number_format($grandTotal, 0, '.', ','),
      'grand_perbaikan_r' => number_format($grandPerbaikanR, 0, '.', ','),
      'grand_perbaikan_s' => number_format($grandPerbaikanS, 0, '.', ','),
      'grand_perbaikan_t' => number_format($grandPerbaikanT, 0, '.', ','),
      'grand_sparepart_r' => number_format($grandSparepartR, 0, '.', ','),
      'grand_sparepart_s' => number_format($grandSparepartS, 0, '.', ','),
      'grand_sparepart_t' => number_format($grandSparepartT, 0, '.', ','),
      'grand_lain_r' => number_format($grandLainR, 0, '.', ','),
      'grand_lain_s' => number_format($grandLainS, 0, '.', ','),
      'grand_lain_t' => number_format($grandLainT, 0, '.', ','),
      'grand_ppn' => number_format($grandPPN, 0, '.', ','),
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

    return redirect('administrasi/laporan-rekap-estimasi')->with('datafilter', $dataArray);
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

    $tglAwal = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
    $tglAkhir = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
    $periodeStr = date("d-M-Y", strtotime($tglAwal)) . ' s/d ' . date("d-M-Y", strtotime($tglAkhir));

    $fileName = 'Laporan_Rekap_Estimasi_' . date('Ymd') . '.xlsx';

    return Excel::download(new LaporanRekapEstimasiExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */

  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Rekap Estimasi';
    $filters = $request->all();

    $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal)->format('Y-m-d');
    $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir)->format('Y-m-d');

    // Query data - PERBAIKAN: tambahkan kode_claim dan nama_cabang di GROUP BY
    $query = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.nama_pelanggan',
        DB::raw('COUNT(1) as unit'),
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
        DB::raw('SUM(k.ppn) as ppn'),
        DB::raw('SUM(k.total) as total'),
      ])
      ->groupBy('k.nama_pelanggan');

    // Filtering
    if (!empty($request->tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal)->format('Y-m-d');
        $query->whereDate('k.tanggal', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($request->tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir)->format('Y-m-d');
        $query->whereDate('k.tanggal', '<=', $endDate);
      } catch (\Exception $e) {
      }
    }

    $datas = $query->orderBy('k.nama_pelanggan', 'asc')->get();

    // Periode string untuk tampilan
    $periodeStr = '';
    if (!empty($request->tgl_awal) && !empty($request->tgl_akhir)) {
      $periodeStr = date("d-M-Y", strtotime($startDate)) . ' s/d ' . date("d-M-Y", strtotime($endDate));
    }

    $pages = 'content.administrasi.laporan.laporan-rekap-estimasi-print';

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
