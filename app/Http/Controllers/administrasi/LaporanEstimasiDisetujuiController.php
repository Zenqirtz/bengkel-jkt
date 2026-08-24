<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanEstimasiDisetujuiExport;

use App\Helpers\Helpers as Helper;

class LaporanEstimasiDisetujuiController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanEstimasiDisetujui(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Estimasi Disetujui';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-estimasi-disetujui', [
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

    $query = DB::table('v_rep_estimasi_disetujui as k')
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
        'k.nama_pelanggan',
        'k.tgl_konsep',
        'k.tgl_estimasi',
        'k.tgl_persetujuan',
        'k.total_perbaikan',
        'k.total_sparepart',
        'k.total_lain',
        'k.total',
        'k.total_perbaikan_s',
        'k.total_sparepart_s',
        'k.total_lain_s',
        'k.total_s',
        'k.total_or_ass',
      ])
      ->orderBy('k.tgl_estimasi', 'asc')
      ->get();

    // Susun data untuk DataTables
    $data = [];
    $no = 0;
    $grandTotalPerbaikan = 0;
    $grandTotalSparepart = 0;
    $grandTotalLain = 0;
    $grandTotal = 0;
    $grandTotalPerbaikanS = 0;
    $grandTotalSparepartS = 0;
    $grandTotalLainS = 0;
    $grandTotalS = 0;
    $grandTotalOrAss = 0;

    foreach ($datas as $row) {
      $data[] = [
        'no' => ++$no,
        'kode_spk' => $row->kode_spk,
        'no_polisi' => $row->no_polisi,
        'kode_estimasi' => $row->kode_estimasi,
        'nama_pelanggan' => $row->nama_pelanggan,
        'tgl_konsep' => blank($row->tgl_konsep) ? '' : date("d/m/Y", strtotime($row->tgl_konsep)),
        'tgl_estimasi' => blank($row->tgl_estimasi) ? '' : date("d/m/Y", strtotime($row->tgl_estimasi)),
        'tgl_persetujuan' => blank($row->tgl_persetujuan) ? '' : date("d/m/Y", strtotime($row->tgl_persetujuan)),
        'total_perbaikan' => number_format($row->total_perbaikan, 0, ',', '.'),
        'total_sparepart' => number_format($row->total_sparepart, 0, ',', '.'),
        'total_lain' => number_format($row->total_lain, 0, ',', '.'),
        'total' => number_format($row->total, 0, ',', '.'),
        'total_perbaikan_s' => number_format($row->total_perbaikan_s, 0, ',', '.'),
        'total_sparepart_s' => number_format($row->total_sparepart_s, 0, ',', '.'),
        'total_lain_s' => number_format($row->total_lain_s, 0, ',', '.'),
        'total_s' => number_format($row->total_s, 0, ',', '.'),
        'total_or_ass' => number_format($row->total_or_ass, 0, ',', '.'),
      ];

      $grandTotalPerbaikan += $row->total_perbaikan;
      $grandTotalSparepart += $row->total_sparepart;
      $grandTotalLain += $row->total_lain;
      $grandTotal += $row->total;
      $grandTotalPerbaikanS += $row->total_perbaikan_s;
      $grandTotalSparepartS += $row->total_sparepart_s;
      $grandTotalLainS += $row->total_lain_s;
      $grandTotalS += $row->total_s;
      $grandTotalOrAss += $row->total_or_ass;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_total_perbaikan' => number_format($grandTotalPerbaikan, 0, ',', '.'),
      'grand_total_sparepart' => number_format($grandTotalSparepart, 0, ',', '.'),
      'grand_total_lain' => number_format($grandTotalLain, 0, ',', '.'),
      'grand_total' => number_format($grandTotal, 0, ',', '.'),
      'grand_total_perbaikan_s' => number_format($grandTotalPerbaikanS, 0, ',', '.'),
      'grand_total_sparepart_s' => number_format($grandTotalSparepartS, 0, ',', '.'),
      'grand_total_lain_s' => number_format($grandTotalLainS, 0, ',', '.'),
      'grand_total_s' => number_format($grandTotalS, 0, ',', '.'),
      'grand_total_or_ass' => number_format($grandTotalOrAss, 0, ',', '.'),
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

    return redirect('administrasi/laporan-estimasi-disetujui')->with('datafilter', $dataArray);
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

    $fileName = 'Laporan_Estimasi_Disetujui_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanEstimasiDisetujuiExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Estimasi Disetujui';
    $filters = $request->all();

    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rep_estimasi_disetujui as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_spk',
        'k.no_polisi',
        'k.kode_estimasi',
        'k.nama_pelanggan',
        'k.tgl_konsep',
        'k.tgl_estimasi',
        'k.tgl_persetujuan',
        'k.total_perbaikan',
        'k.total_sparepart',
        'k.total_lain',
        'k.total',
        'k.total_perbaikan_s',
        'k.total_sparepart_s',
        'k.total_lain_s',
        'k.total_s',
        'k.total_or_ass',
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

    $pages = 'content.administrasi.laporan.laporan-estimasi-disetujui-print';

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
