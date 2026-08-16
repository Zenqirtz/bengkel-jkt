<?php

namespace App\Http\Controllers\administrasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKomisiSurveyorExport;

class LaporanKomisiSurveyorController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanKomisiSurveyor(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Komisi Surveyor';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.administrasi.laporan.laporan-komisi-surveyor', [
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

    $query = DB::table('v_rep_komisi_surveyor as k')
      ->where('k.kode_cabang', $user_cabang);

    // Ambil tanggal dari request atau session
    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter.tgl_akhir', date("d/m/Y"));

    // Filter berdasarkan tanggal masuk
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '>=', $startDate);
      } catch (\Exception $e) {
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $endDate);
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
        'k.tgl_masuk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.no_polis',
        'k.kode_estimasi',
        'k.total_sparepart',
        'k.total_lain',
        'k.total_perbaikan',
      ])
      ->orderBy('k.tgl_masuk', 'asc')
      ->get();

    // Susun data untuk DataTables
    $data = [];
    $no = 0;
    $grandJasa = 0;
    $grandSparepart = 0;
    $grandLain = 0;
    $grandJumlah = 0;

    foreach ($datas as $row) {
      // Hitung jasa = total_perbaikan - (total_sparepart + total_lain)
      // Gunakan abs() untuk menghilangkan minus
      $totalJasa = abs($row->total_perbaikan - ($row->total_sparepart + $row->total_lain));

      $data[] = [
        'no' => ++$no,
        'kode_spk' => $row->kode_spk,
        'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
        'no_polisi' => $row->no_polisi,
        'merek_tipe' => $row->merek_tipe,
        'nama_pelanggan' => $row->nama_pelanggan,
        'no_polis' => $row->no_polis,
        'kode_estimasi' => $row->kode_estimasi,
        'total_jasa' => number_format($totalJasa, 0, ',', '.'),
        'total_sparepart' => number_format($row->total_sparepart, 0, ',', '.'),
        'total_lain' => number_format($row->total_lain, 0, ',', '.'),
        'total_perbaikan' => number_format($row->total_perbaikan, 0, ',', '.'),
      ];

      $grandJasa += $totalJasa;
      $grandSparepart += $row->total_sparepart;
      $grandLain += $row->total_lain;
      $grandJumlah += $row->total_perbaikan;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
      'grand_jasa' => number_format($grandJasa, 0, ',', '.'),
      'grand_sparepart' => number_format($grandSparepart, 0, ',', '.'),
      'grand_lain' => number_format($grandLain, 0, ',', '.'),
      'grand_jumlah' => number_format($grandJumlah, 0, ',', '.'),
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

    return redirect('administrasi/laporan-komisi-surveyor')->with('datafilter', $dataArray);
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

    $fileName = 'Laporan_Komisi_Surveyor_' . date('Ymd_His') . '.xlsx';

    return Excel::download(new LaporanKomisiSurveyorExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Komisi Surveyor';
    $filters = $request->all();

    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rep_komisi_surveyor as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_spk',
        'k.tgl_masuk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.no_polis',
        'k.kode_estimasi',
        'k.total_sparepart',
        'k.total_lain',
        'k.total_perbaikan',
      ]);

    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('k.tgl_masuk', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('k.tgl_masuk', '<=', $endDate);
    }

    $datas = $query->orderBy('k.tgl_masuk', 'asc')->get();

    $pages = 'content.administrasi.laporan.laporan-komisi-surveyor-print';

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
