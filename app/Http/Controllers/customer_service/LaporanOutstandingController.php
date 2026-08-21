<?php

namespace App\Http\Controllers\customer_service;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Spk;
use App\Models\Parameter;
use App\Models\LogActivity;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; // EXPORT EXCEL
use App\Exports\LaporanOutstandingExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class LaporanOutstandingController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanOutstanding(): View
  {
    $isList = Helper::AuthIsPerm("list");
    $isAdd = Helper::AuthIsPerm("add");
    $isEdit = Helper::AuthIsPerm("edit");
    $isDel = Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = Helper::getTitleMenu($path) ?? 'Kendaraan';

    $user_cabang = session('kd_cabang');

    $months = Helper::listMonths();
    $years = Helper::listYears();
    $jenis_laporan = [
      '' => 'Pilih Jenis Laporan',
      'periode' => 'Per Periode',
      'tahun' => 'Per Tahun'
    ];

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['jenis_laporan'] = '';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
      $datafilter['bulan'] = date("m");
      $datafilter['tahun2'] = date("Y");
      $datafilter['tahun'] = date("Y");
    }

    return view('content.customer-service.laporan.laporan-outstanding', [
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

    if ($request->filled('jenis_laporan')) {

      if($request->jenis_laporan == "periode") {
        // Base query
        $base = DB::table('v_rpt_outstanding_or as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        if ($request->filled('tgl_awal')) {
          $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('k.tgl_invoice', '<=', $startDate);
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.id',
            'k.kode_spk',
            'k.no_polisi',
            'k.merek_tipe',
            'k.nama_pelanggan',
            'k.tertanggung',
            'k.no_invoice',
            'k.tgl_invoice',
            'k.total_or',
            'k.kode_keluar',
          ])
          ->orderBy('k.tgl_invoice', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'kode_spk' => $row->kode_spk,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'nama_pelanggan' => $row->nama_pelanggan,
              'tertanggung' => $row->tertanggung,
              'no_invoice' => $row->no_invoice,
              'tgl_invoice' => blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)),
              'total_or' => number_format($row->total_or,0,".",","),
              'kode_keluar' => $row->kode_keluar,
            ];
        }
      } elseif($request->jenis_laporan == "tahun") {
        // Base query
        $base = DB::table('v_rpt_outstanding_or')
          ->select(
              'nama_pelanggan',
              // Gunakan DB::raw untuk CASE WHEN expression
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 1 THEN total_or ELSE 0 END) AS JAN"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 2 THEN total_or ELSE 0 END) AS FEB"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 3 THEN total_or ELSE 0 END) AS MAR"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 4 THEN total_or ELSE 0 END) AS APR"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 5 THEN total_or ELSE 0 END) AS MEI"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 6 THEN total_or ELSE 0 END) AS JUN"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 7 THEN total_or ELSE 0 END) AS JUL"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 8 THEN total_or ELSE 0 END) AS AGS"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 9 THEN total_or ELSE 0 END) AS SEP"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 10 THEN total_or ELSE 0 END) AS OKT"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 11 THEN total_or ELSE 0 END) AS NOV"),
              DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 12 THEN total_or ELSE 0 END) AS DES"),
              DB::raw("SUM(total_or) AS Total")
          )
          ->where('kode_cabang', $user_cabang)
          ->whereYear('tgl_invoice', $request->tahun) // Helper Laravel untuk YEAR()
          ->groupBy('nama_pelanggan');

        // Filtering (search global)
        $query = (clone $base);

        // Total baris tanpa filter
        $totalData = (clone $query)->count();

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count();

        // Ambil data halaman saat ini
        $datas = $query->orderBy('nama_pelanggan', 'asc')->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'nama_pelanggan' => $row->nama_pelanggan,
              'JAN' => number_format($row->JAN,0,".",","),
              'FEB' => number_format($row->FEB,0,".",","),
              'MAR' => number_format($row->MAR,0,".",","),
              'APR' => number_format($row->APR,0,".",","),
              'MEI' => number_format($row->MEI,0,".",","),
              'JUN' => number_format($row->JUN,0,".",","),
              'JUL' => number_format($row->JUL,0,".",","),
              'AGS' => number_format($row->AGS,0,".",","),
              'SEP' => number_format($row->SEP,0,".",","),
              'OKT' => number_format($row->OKT,0,".",","),
              'NOV' => number_format($row->NOV,0,".",","),
              'DES' => number_format($row->DES,0,".",","),
              'Total' => number_format($row->Total,0,".",","),
            ];
        }
      }

    }

    // ✅ Always return full DataTables structure, even if no results
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
        'jenis_laporan' => 'required'
      ],
      [ // custom messages
        'jenis_laporan.required'    => 'Jenis Laporan wajib diisi.',
      ]
    );

    $dataArray['jenis_laporan'] = $request->jenis_laporan;

    if ($dataArray['jenis_laporan'] == "periode") {
      // $dataArray['tgl_awal'] = blank($request->tgl_awal) ? date("d/m/Y") : Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
      $dataArray['tgl_awal'] = $request->tgl_awal;
      $dataArray['tgl_akhir'] = $request->tgl_akhir;
      $dataArray['bulan'] = date("m");
      $dataArray['tahun2'] = date("Y");
      $dataArray['tahun'] = date("Y");
    } elseif ($dataArray['jenis_laporan'] == "bulan") {
      $dataArray['bulan'] = $request->bulan;
      $dataArray['tahun2'] = $request->tahun2;
      $dataArray['tgl_awal'] = date("d/m/Y");
      $dataArray['tgl_akhir'] = date("d/m/Y");
      $dataArray['tahun'] = date("Y");
    } elseif ($dataArray['jenis_laporan'] == "tahun") {
      $dataArray['tahun'] = $request->tahun;
      $dataArray['tgl_awal'] = date("d/m/Y");
      $dataArray['tgl_akhir'] = date("d/m/Y");
      $dataArray['bulan'] = date("m");
      $dataArray['tahun2'] = date("Y");
    }

    ## Log Activity
    $desc = "View Laporan Outstanding OR";
    LogActivity::saveLogActivity($desc, $dataArray);

    return redirect('customer-service/laporan-outstanding')->with('datafilter', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');
    
    // --- TAMBAHAN: Ambil Nama Cabang untuk Header Excel ---
    // Asumsi ada helper atau query untuk ambil nama cabang. 
    // Jika tidak ada tabel cabang, ganti dengan hardcode string atau session nama cabang.
    // $cabangInfo = DB::table('cabang')->where('kode_cabang', $user_cabang)->first();
    // $namaCabang = $cabangInfo ? $cabangInfo->nama_cabang : $user_cabang;

    // Siapkan struktur data cabang
    $cabangData = [
        'kode' => $user_cabang,
        'nama' => $namaCabang
    ];
    // -----------------------------------------------------

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    if($request->jenis_laporan == "periode") {
      $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
      $periodeStr = $tglAwal;
    } elseif($request->jenis_laporan == "bulan") {
      $months = Helper::listMonths();
      $periodeStr = $months[$request->bulan] . ' ' . $request->tahun2;
    } elseif($request->jenis_laporan == "tahun") {
      $periodeStr = $request->tahun;
    }
    // ---------------------------------------

    if($request->jenis_laporan == "periode") {
      $fileName = 'Laporan_Outstanding_' . date('Ymd_His') . '.xlsx';
    } elseif($request->jenis_laporan == "tahun") {
      $fileName = 'Laporan_Rekap_Outstanding_' . date('Ymd_His') . '.xlsx';
    }

    ## Log Activity
    $desc = "Export Laporan Outstanding OR";
    LogActivity::saveLogActivity($desc, $filters);

    // Masukkan $periodeStr dan $cabangData ke dalam use App\Helpers\Helpers as Helper;

class Export
    return Excel::download(new LaporanOutstandingExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Kendaraan';

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    if($filters['jenis_laporan'] == "periode") {
      $tglAwal = date('d/m/Y', strtotime($filters['tgl_awal']));
      $periodeStr = $tglAwal;
    } elseif($filters['jenis_laporan'] == "bulan") {
      $months = Helper::listMonths();
      $periodeStr = $months[$filters['bulan']] . ' ' . $filters['tahun2'];
    } elseif($filters['jenis_laporan'] == "tahun") {
      $periodeStr = $filters['tahun'];
    }
    // ---------------------------------------

    // --- DATA ---
    $datas = [];
    if($filters['jenis_laporan'] == "periode") {
      $query = DB::table('v_rpt_outstanding_or as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.id',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.tertanggung',
        'k.no_invoice',
        'k.tgl_invoice',
        'k.total_or',
        'k.kode_keluar',
      ])
      ->orderBy('k.tgl_invoice', 'asc');

      // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
      if (!empty($filters['jenis_laporan'])) {
          if($filters['jenis_laporan'] == "periode") {
              try {
                  $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
                  $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
                  $query->whereDate('k.tgl_invoice', '>=', $startDate);
                  $query->whereDate('k.tgl_invoice', '<=', $endDate);
              } catch (\Exception $e) {}
          } elseif($filters['jenis_laporan'] == "bulan") {
              $query->whereMonth('k.tgl_invoice', $filters['bulan']);
              $query->whereYear('k.tgl_invoice', $filters['tahun2']);
          } elseif($filters['jenis_laporan'] == "tahun") {
              $query->whereYear('k.tgl_invoice', $filters['tahun']);
          }
      }

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-outstanding-periode-print';
    } elseif($filters['jenis_laporan'] == "tahun") {
      $query = DB::table('v_rpt_outstanding_or')
      ->select(
          'nama_pelanggan',
          // Gunakan DB::raw untuk CASE WHEN expression
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 1 THEN total_or ELSE 0 END) AS JAN"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 2 THEN total_or ELSE 0 END) AS FEB"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 3 THEN total_or ELSE 0 END) AS MAR"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 4 THEN total_or ELSE 0 END) AS APR"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 5 THEN total_or ELSE 0 END) AS MEI"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 6 THEN total_or ELSE 0 END) AS JUN"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 7 THEN total_or ELSE 0 END) AS JUL"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 8 THEN total_or ELSE 0 END) AS AGS"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 9 THEN total_or ELSE 0 END) AS SEP"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 10 THEN total_or ELSE 0 END) AS OKT"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 11 THEN total_or ELSE 0 END) AS NOV"),
          DB::raw("SUM(CASE WHEN MONTH(tgl_invoice) = 12 THEN total_or ELSE 0 END) AS DES"),
          DB::raw("SUM(total_or) AS Total")
      )
      ->where('kode_cabang', $user_cabang)
      ->whereYear('tgl_invoice', $filters['tahun']) // Helper Laravel untuk YEAR()
      ->groupBy('nama_pelanggan')
      ->orderBy('nama_pelanggan', 'asc');

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-outstanding-tahun-print';
    }

    ## Log Activity
    $desc = "Print Laporan Outstanding OR";
    LogActivity::saveLogActivity($desc, $filters);

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