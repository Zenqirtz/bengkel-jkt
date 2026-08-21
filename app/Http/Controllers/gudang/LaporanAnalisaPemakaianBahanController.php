<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanAnalisaPemakaianBahanExport;

use App\Helpers\Helpers as Helper;

class LaporanAnalisaPemakaianBahanController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanAnalisaPemakaianBahan(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Laporan Analisa Pemakaian Bahan';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_analisa_pemakaian_bahan');
    if (empty($datafilter)) {
      $datafilter['jenis_report'] = 'Rekap';
      $datafilter['tahun'] = date("Y");
      $datafilter['bulan'] = date("m");
    }

    // Generate years untuk dropdown (10 tahun terakhir)
    $currentYear = date('Y');
    $years = [];
    for ($i = 0; $i < 10; $i++) {
      $year = $currentYear - $i;
      $years[$year] = $year;
    }

    // Data bulan
    $months = [
      '01' => 'Januari',
      '02' => 'Februari',
      '03' => 'Maret',
      '04' => 'April',
      '05' => 'Mei',
      '06' => 'Juni',
      '07' => 'Juli',
      '08' => 'Agustus',
      '09' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember'
    ];

    return view('content.gudang.laporan.laporan-analisa-pemakaian-bahan', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'datafilter' => $datafilter,
      'years' => $years,
      'months' => $months,
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  // public function index(Request $request): JsonResponse
  // {
  //   try {
  //     $user_cabang = session('kd_cabang');

  //     $jenis_report = $request->filled('jenis_report')
  //       ? $request->jenis_report
  //       : session('datafilter_analisa_pemakaian_bahan.jenis_report', 'Rekap');

  //     $tahun = $request->filled('tahun')
  //       ? $request->tahun
  //       : session('datafilter_analisa_pemakaian_bahan.tahun', date('Y'));

  //     $bulan = $request->filled('bulan')
  //       ? $request->bulan
  //       : session('datafilter_analisa_pemakaian_bahan.bulan', date('m'));

  //     // Query akan menggunakan view v_rep_rekap_analisa_bahan
  //     // Karena view belum ada, kita tetap buat struktur query-nya
  //     $query = DB::table('v_rep_rekap_analisa_bahan as rab')
  //       ->where('rab.kode_cabang', $user_cabang);

  //     // Filter berdasarkan tahun dan bulan
  //     if (!empty($tahun) && !empty($bulan)) {
  //       $query->whereYear('rab.tanggal', $tahun)
  //             ->whereMonth('rab.tanggal', $bulan);
  //     }

  //     $totalData = (clone $query)->count();
  //     $totalFiltered = (clone $query)->count();

  //     if ($jenis_report === 'Rekap') {
  //       // Untuk rekap, group by nama_bahan
  //       $datas = $query
  //         ->select([
  //           'rab.nama_bahan',
  //           DB::raw('SUM(rab.qty) as qty'),
  //           DB::raw('AVG(rab.harga) as harga'),
  //           DB::raw('SUM(rab.jumlah) as jumlah'),
  //           'rab.satuan',
  //           DB::raw('SUM(rab.qty_per_point) as qty_per_point'),
  //           DB::raw('SUM(rab.rupiah_per_point) as rupiah_per_point')
  //         ])
  //         ->groupBy('rab.nama_bahan', 'rab.satuan')
  //         ->orderBy('rab.nama_bahan', 'asc')
  //         ->get();
  //     } else {
  //       // Untuk rinci, tampilkan semua detail
  //       $datas = $query
  //         ->select([
  //           'rab.nama_bahan',
  //           'rab.qty',
  //           'rab.harga',
  //           'rab.jumlah',
  //           'rab.satuan',
  //           'rab.qty_per_point',
  //           'rab.rupiah_per_point'
  //         ])
  //         ->orderBy('rab.nama_bahan', 'asc')
  //         ->get();
  //     }

  //     $data = [];
  //     $no = 0;

  //     foreach ($datas as $row) {
  //       $data[] = [
  //         'no' => ++$no,
  //         'nama_bahan' => $row->nama_bahan,
  //         'qty' => number_format($row->qty, 2, '.', '.'),
  //         'harga' => number_format($row->harga, 0, '.', '.'),
  //         'jumlah' => number_format($row->jumlah, 0, '.', '.'),
  //         'satuan' => $row->satuan ?? '',
  //         'qty_per_point' => number_format($row->qty_per_point, 2, '.', '.'),
  //         'rupiah_per_point' => number_format($row->rupiah_per_point, 0, '.', '.')
  //       ];
  //     }

  //     // Hitung jumlah panel
  //     $jumlahPanel = DB::table('v_rep_rekap_analisa_bahan')
  //       ->where('kode_cabang', $user_cabang)
  //       ->whereYear('tanggal', $tahun)
  //       ->whereMonth('tanggal', $bulan)
  //       ->value('jumlah_panel') ?? 0;

  //     // Format periode
  //     $months = [
  //       '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
  //       '04' => 'April', '05' => 'Mei', '06' => 'Juni',
  //       '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
  //       '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
  //     ];
  //     $periode = $months[$bulan] . ' - ' . $tahun;

  //     return response()->json([
  //       'draw' => intval($request->input('draw')),
  //       'recordsTotal' => intval($totalData),
  //       'recordsFiltered' => intval($totalFiltered),
  //       'data' => $data,
  //       'jumlah_panel' => number_format($jumlahPanel, 2, '.', '.'),
  //       'periode' => $periode,
  //     ]);

  //   } catch (\Exception $e) {
  //     // Jika ada error (view belum ada), return data kosong
  //     return response()->json([
  //       'draw' => intval($request->input('draw')),
  //       'recordsTotal' => 0,
  //       'recordsFiltered' => 0,
  //       'data' => [],
  //     ]);
  //   }
  // }
  public function index(Request $request): JsonResponse
  {
    try {
      $user_cabang = session('kd_cabang');

      // Samakan persis dengan exportExcel() & printData(): ambil dari request langsung,
      // pakai ?: bukan ?? / fallback session supaya string kosong juga ke-fallback
      $jenis_report = $request->input('jenis_report') ?: 'Rekap';
      $tahun = $request->input('tahun') ?: date('Y');
      $bulan = $request->input('bulan') ?: date('m');

      $query = DB::table('v_rep_rekap_analisa_bahan as rab')
        ->where('rab.kode_cabang', $user_cabang)
        ->whereYear('rab.tanggal', $tahun)
        ->whereMonth('rab.tanggal', $bulan);

      $jumlahPanel = (clone $query)->value('jumlah_panel') ?? 0;

      if ($jenis_report === 'Rekap') {
        $datas = (clone $query)
          ->select([
            'rab.nama_bahan',
            DB::raw('SUM(rab.qty) as qty'),
            DB::raw('AVG(rab.harga) as harga'),
            DB::raw('SUM(rab.jumlah) as jumlah'),
            'rab.satuan',
          ])
          ->groupBy('rab.nama_bahan', 'rab.satuan')
          ->orderBy('rab.nama_bahan', 'asc')
          ->get();
      } else {
        $datas = (clone $query)
          ->select([
            'rab.tanggal',
            'rab.nama_bahan',
            'rab.qty',
            'rab.harga',
            'rab.jumlah',
            'rab.satuan',
          ])
          ->orderBy('rab.nama_bahan', 'asc')
          ->get();
      }

      $data = [];
      $no = 0;
      $totalJumlah = 0;
      $totalQtyPoint = 0;
      $totalRupiahPoint = 0;

      foreach ($datas as $row) {
        $qtyPerPoint = $jumlahPanel > 0 ? $row->qty / $jumlahPanel : 0;
        $rupiahPerPoint = $jumlahPanel > 0 ? $row->jumlah / $jumlahPanel : 0;

        $totalJumlah += $row->jumlah;
        $totalQtyPoint += $qtyPerPoint;
        $totalRupiahPoint += $rupiahPerPoint;

        $data[] = [
          'no' => ++$no,
          'tanggal' => $row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y') : '',
          'nama_bahan' => $row->nama_bahan,
          'qty' => number_format($row->qty, 2, '.', '.'),
          'harga' => number_format($row->harga, 0, '.', '.'),
          'jumlah' => number_format($row->jumlah, 0, '.', '.'),
          'satuan' => $row->satuan ?? '',
          'qty_per_point' => number_format($qtyPerPoint, 2, '.', '.'),
          'rupiah_per_point' => number_format($rupiahPerPoint, 0, '.', '.'),
          'is_total' => false
        ];
      }

      // Tambahkan baris Grand Total jika ada data
      if (count($data) > 0) {
        $data[] = [
          'no' => '',
          'tanggal' => '',
          'nama_bahan' => 'Grand Total',
          'qty' => '',
          'harga' => '',
          'jumlah' => number_format($totalJumlah, 0, '.', '.'),
          'satuan' => '',
          'qty_per_point' => number_format($totalQtyPoint, 2, '.', '.'),
          'rupiah_per_point' => number_format($totalRupiahPoint, 0, '.', '.'),
          'is_total' => true
        ];
      }

      $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
      ];
      $periode = $months[$bulan] . ' - ' . $tahun;

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => count($data),
        'recordsFiltered' => count($data),
        'data' => $data,
        'jumlah_panel' => number_format($jumlahPanel, 2, '.', '.'),
        'periode' => $periode,
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
      ]);
    }
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
        'jenis_report' => 'required',
        'tahun' => 'required',
        'bulan' => 'required'
      ],
      [
        'jenis_report.required' => 'Jenis Report wajib dipilih.',
        'tahun.required' => 'Tahun wajib dipilih.',
        'bulan.required' => 'Bulan wajib dipilih.'
      ]
    );

    $dataArray['jenis_report'] = $request->jenis_report;
    $dataArray['tahun'] = $request->tahun;
    $dataArray['bulan'] = $request->bulan;

    return redirect('gudang/laporan-analisa-pemakaian-bahan')->with('datafilter_analisa_pemakaian_bahan', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  // public function exportExcel(Request $request)
  // {
  //   try {
  //     $user_cabang = session('kd_cabang');
  //     $namaCabang = session('nm_cabang');

  //     $cabangData = [
  //       'kode' => $user_cabang,
  //       'nama' => $namaCabang
  //     ];

  //     $filters = $request->all();

  //     $tahun = $request->input('tahun', date('Y'));
  //     $bulan = $request->input('bulan', date('m'));

  //     // Nama bulan
  //     $months = [
  //       '01' => 'Januari',
  //       '02' => 'Februari',
  //       '03' => 'Maret',
  //       '04' => 'April',
  //       '05' => 'Mei',
  //       '06' => 'Juni',
  //       '07' => 'Juli',
  //       '08' => 'Agustus',
  //       '09' => 'September',
  //       '10' => 'Oktober',
  //       '11' => 'November',
  //       '12' => 'Desember'
  //     ];

  //     $periodeStr = $months[$bulan] . ' - ' . $tahun;

  //     $fileName = 'Laporan_Analisa_Pemakaian_Bahan_' . date('Ymd_His') . '.xlsx';

  //     return Excel::download(
  //       new LaporanAnalisaPemakaianBahanExport($filters, $cabangData, $periodeStr),
  //       $fileName
  //     );
  //   } catch (\Exception $e) {
  //     return redirect()->back()->with('error', 'View database belum tersedia. Silakan hubungi administrator.');
  //   }
  // }
  public function exportExcel(Request $request)
  {
    try {
      $user_cabang = session('kd_cabang');
      $namaCabang = session('nm_cabang');

      $cabangData = [
        'kode' => $user_cabang,
        'nama' => $namaCabang
      ];

      // pakai ?: bukan ?? agar string kosong juga ke-fallback
      $tahun = $request->input('tahun') ?: date('Y');
      $bulan = $request->input('bulan') ?: date('m');
      $jenis_report = $request->input('jenis_report') ?: 'Rekap';

      // satu sumber filter yang sama, dipakai untuk query DAN untuk label periode
      $filters = [
        'tahun' => $tahun,
        'bulan' => $bulan,
        'jenis_report' => $jenis_report,
      ];

      $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
      ];

      $periodeStr = $months[$bulan] . ' - ' . $tahun;

      $fileName = 'Laporan_Analisa_Pemakaian_Bahan_' . date('Ymd_His') . '.xlsx';

      return Excel::download(
        new LaporanAnalisaPemakaianBahanExport($filters, $cabangData, $periodeStr),
        $fileName
      );
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Gagal export: ' . $e->getMessage());
    }
  }

  /**
   * Print data.
   */
  // public function printData(Request $request)
  // {
  //   try {
  //     $user_cabang = session('kd_cabang');
  //     $namaCabang = session('nm_cabang');

  //     $title = 'Laporan Analisa Pemakaian Bahan';
  //     $filters = $request->all();

  //     $tahun = $filters['tahun'] ?? date('Y');
  //     $bulan = $filters['bulan'] ?? date('m');
  //     $jenis_report = $filters['jenis_report'] ?? 'Rekap';

  //     // Nama bulan
  //     $months = [
  //       '01' => 'Januari',
  //       '02' => 'Februari',
  //       '03' => 'Maret',
  //       '04' => 'April',
  //       '05' => 'Mei',
  //       '06' => 'Juni',
  //       '07' => 'Juli',
  //       '08' => 'Agustus',
  //       '09' => 'September',
  //       '10' => 'Oktober',
  //       '11' => 'November',
  //       '12' => 'Desember'
  //     ];

  //     $periodeStr = $months[$bulan] . ' - ' . $tahun;

  //     // $query = DB::table('v_rep_rekap_analisa_bahan as rab')
  //     $query = DB::table('v_rekap_analisa_bahan as rab')
  //       ->where('rab.kode_cabang', $user_cabang);

  //     if (!empty($tahun) && !empty($bulan)) {
  //       $query->whereYear('rab.tanggal', $tahun)
  //         ->whereMonth('rab.tanggal', $bulan);
  //     }

  //     // Hitung jumlah panel (asumsi ada field)
  //     // $jumlahPanel = DB::table('v_rep_rekap_analisa_bahan')
  //     $jumlahPanel = DB::table('v_rekap_analisa_bahan')
  //       ->where('kode_cabang', $user_cabang)
  //       ->whereYear('tanggal', $tahun)
  //       ->whereMonth('tanggal', $bulan)
  //       ->value('jumlah_panel') ?? 0;

  //     if ($jenis_report === 'Rekap') {
  //       $datas = $query
  //         ->select([
  //           'rab.nama_bahan',
  //           DB::raw('SUM(rab.qty) as qty'),
  //           DB::raw('AVG(rab.harga) as harga'),
  //           DB::raw('SUM(rab.jumlah) as jumlah'),
  //           'rab.satuan',
  //           DB::raw('SUM(rab.qty_per_point) as qty_per_point'),
  //           DB::raw('SUM(rab.rupiah_per_point) as rupiah_per_point')
  //         ])
  //         ->groupBy('rab.nama_bahan', 'rab.satuan')
  //         ->orderBy('rab.nama_bahan', 'asc')
  //         ->get();
  //     } else {
  //       $datas = $query
  //         ->select([
  //           'rab.nama_bahan',
  //           'rab.qty',
  //           'rab.harga',
  //           'rab.jumlah',
  //           'rab.satuan',
  //           'rab.qty_per_point',
  //           'rab.rupiah_per_point'
  //         ])
  //         ->orderBy('rab.nama_bahan', 'asc')
  //         ->get();
  //     }

  //     $pageConfigs = ['myLayout' => 'blank'];
  //     return view('content.gudang.laporan.laporan-analisa-pemakaian-bahan-print', [
  //       'title' => $title,
  //       'namaCabang' => $namaCabang,
  //       'periodeStr' => $periodeStr,
  //       'jumlahPanel' => $jumlahPanel,
  //       'no' => 1,
  //       'datas' => $datas,
  //       'datafilter' => $filters,
  //       'pageConfigs' => $pageConfigs,
  //     ]);
  //   } catch (\Exception $e) {
  //     return redirect()->back()->with('error', 'View database belum tersedia. Silakan hubungi administrator.');
  //   }
  // }
  public function printData(Request $request)
  {
    try {
      $user_cabang = session('kd_cabang');
      $namaCabang = session('nm_cabang');

      $title = 'Laporan Analisa Pemakaian Bahan';
      $filters = $request->all();

      $tahun = $filters['tahun'] ?? date('Y');
      $bulan = $filters['bulan'] ?? date('m');
      $jenis_report = $filters['jenis_report'] ?? 'Rekap';

      $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
      ];

      $periodeStr = $months[$bulan] . ' - ' . $tahun;

      // Jumlah panel: sumber sama persis dengan index()
      $query = DB::table('v_rep_rekap_analisa_bahan as rab')
        ->where('rab.kode_cabang', $user_cabang)
        ->whereYear('rab.tanggal', $tahun)
        ->whereMonth('rab.tanggal', $bulan);

      $jumlahPanel = (clone $query)->value('jumlah_panel') ?? 0;

      if ($jenis_report === 'Rekap') {
        $rows = (clone $query)
          ->select([
            'rab.nama_bahan',
            DB::raw('SUM(rab.qty) as qty'),
            DB::raw('AVG(rab.harga) as harga'),
            DB::raw('SUM(rab.jumlah) as jumlah'),
            'rab.satuan',
          ])
          ->groupBy('rab.nama_bahan', 'rab.satuan')
          ->orderBy('rab.nama_bahan', 'asc')
          ->get();
      } else {
        $rows = (clone $query)
          ->select(['rab.nama_bahan', 'rab.qty', 'rab.harga', 'rab.jumlah', 'rab.satuan'])
          ->orderBy('rab.nama_bahan', 'asc')
          ->get();
      }

      $datas = $rows->map(function ($row) use ($jumlahPanel) {
        $row->qty_per_point = $jumlahPanel > 0 ? $row->qty / $jumlahPanel : 0;
        $row->rupiah_per_point = $jumlahPanel > 0 ? $row->jumlah / $jumlahPanel : 0;
        return $row;
      });

      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.gudang.laporan.laporan-analisa-pemakaian-bahan-print', [
        'title' => $title,
        'namaCabang' => $namaCabang,
        'periodeStr' => $periodeStr,
        'jumlahPanel' => $jumlahPanel,
        'no' => 1,
        'datas' => $datas,
        'datafilter' => $filters,
        'pageConfigs' => $pageConfigs,
      ]);
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'View database belum tersedia. Silakan hubungi administrator.');
    }
  }
}
