<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRincianEstimasiExport;

class LaporanRincianEstimasiController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanRincianEstimasi(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");

    if (!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Rincian Estimasi';

    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-rincian-estimasi', [
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
    $totalData = (clone $query)->count();
    $totalFiltered = (clone $query)->count();

    // Ambil data
    $datas = $query
      ->select([
        'k.kode_estimasi',
        'k.tanggal',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.perbaikan_r',
        'k.perbaikan_s',
        'k.perbaikan_t',
        'k.total_perbaikan',
        'k.sparepart_r',
        'k.sparepart_s',
        'k.sparepart_t',
        'k.total_sparepart',
        'k.lain_r',
        'k.lain_s',
        'k.lain_t',
        'k.total_lain',
        'k.nama_pelanggan',
        'k.total',
        'k.ppn',
        'k.kode_claim',
        'k.kode_spk',
      ])
      ->orderBy('k.tanggal', 'asc')
      ->orderBy('k.kode_estimasi', 'asc')
      ->get();

    // Susun data untuk DataTables
    $data = [];
    $no = 0;
    $grandPerbaikan = 0;
    $grandSparepart = 0;
    $grandLain = 0;
    $grandTotalR = 0;
    $grandTotalS = 0;
    $grandTotalT = 0;
    $grandPPN = 0;
    $grandTotal = 0;

    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$no,
        'nama_pelanggan' => $row->nama_pelanggan,
        'kode_estimasi' => $row->kode_estimasi,
        'tanggal' => blank($row->tanggal) ? '' : date("d/m/Y", strtotime($row->tanggal)),
        'kode_claim' => $row->kode_claim,
        'kode_spk' => $row->kode_spk,
        'no_polisi' => $row->no_polisi,
        'tipe_kendaraan' => $row->tipe_kendaraan,
        'total_perbaikan' => number_format($row->total_perbaikan, 0, '.', ','),
        'total_sparepart' => number_format($row->total_sparepart, 0, '.', ','),
        'total_lain'      => number_format($row->total_lain, 0, '.', ','),
        'total_r'         => number_format(($row->perbaikan_r + $row->sparepart_r + $row->lain_r), 0, '.', ','),
        'total_s'         => number_format(($row->perbaikan_s + $row->sparepart_s + $row->lain_s), 0, '.', ','),
        'total_t'         => number_format(($row->perbaikan_t + $row->sparepart_t + $row->lain_t), 0, '.', ','),
        'ppn'             => number_format($row->ppn, 0, '.', ','),
        'total'           => number_format($row->total, 0, '.', ','),
      ];

      $grandPerbaikan += $row->total_perbaikan;
      $grandSparepart += $row->total_sparepart;
      $grandLain += $row->total_lain;
      $grandTotalR += ($row->perbaikan_r + $row->sparepart_r + $row->lain_r);
      $grandTotalS += ($row->perbaikan_s + $row->sparepart_s + $row->lain_s);
      $grandTotalT += ($row->perbaikan_t + $row->sparepart_t + $row->lain_t);
      $grandPPN += $row->ppn;
      $grandTotal += $row->total;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_total_perbaikan' => number_format($grandPerbaikan, 0, '.', ','),
      'grand_total_sparepart' => number_format($grandSparepart, 0, '.', ','),
      'grand_total_lain' => number_format($grandLain, 0, '.', ','),
      'grand_total_r' => number_format($grandTotalR, 0, '.', ','),
      'grand_total_s' => number_format($grandTotalS, 0, '.', ','),
      'grand_total_t' => number_format($grandTotalT, 0, '.', ','),
      'grand_ppn' => number_format($grandPPN, 0, '.', ','),
      'grand_total' => number_format($grandTotal, 0, '.', ','),
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

    return redirect('administrasi/laporan-rincian-estimasi')->with('datafilter', $dataArray);
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

    $fileName = 'Laporan_Rincian_Estimasi_' . date('Ymd') . '.xlsx';

    return Excel::download(new LaporanRincianEstimasiExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Rincian Estimasi';
    $filters = $request->all();

    $tglAwal = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
    $tglAkhir = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
    $periodeStr = date("d-M-Y", strtotime($tglAwal)) . ' s/d ' . date("d-M-Y", strtotime($tglAkhir));

    // Query data
    $datas = DB::table('v_rep_estimasi_period as k')
      ->where('k.kode_cabang', $user_cabang)
      ->whereDate('k.tanggal', '>=', $tglAwal)
      ->whereDate('k.tanggal', '<=', $tglAkhir)
      ->select([
        'k.kode_estimasi',
        'k.tanggal',
        'k.no_polisi',
        'k.tipe_kendaraan',
        'k.perbaikan_r',
        'k.perbaikan_s',
        'k.perbaikan_t',
        'k.total_perbaikan',
        'k.sparepart_r',
        'k.sparepart_s',
        'k.sparepart_t',
        'k.total_sparepart',
        'k.lain_r',
        'k.lain_s',
        'k.lain_t',
        'k.total_lain',
        'k.nama_pelanggan',
        'k.total',
        'k.ppn',
        'k.kode_spk',
      ])
      ->orderBy('k.tanggal', 'asc')
      ->orderBy('k.kode_estimasi', 'asc')
      ->get();

    $pages = 'content.administrasi.laporan.laporan-rincian-estimasi-print';

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
