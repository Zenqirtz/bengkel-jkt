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
use App\Exports\LaporanPosisiPerbaikanExport;    // EXPORT EXCEL

class LaporanPosisiPerbaikanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanPosisiPerbaikan(): View
  {
    $isList = \Helper::AuthIsPerm("list");
    $isAdd = \Helper::AuthIsPerm("add");
    $isEdit = \Helper::AuthIsPerm("edit");
    $isDel = \Helper::AuthIsPerm("delete");
    if(!$isList) {
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.error.not-authorized', ['pageConfigs' => $pageConfigs]);
    }

    $path = request()->path();
    $title = \Helper::getTitleMenu($path) ?? 'Posisi Perbaikan';

    $user_cabang = session('kd_cabang');

    $months = \Helper::listMonths();
    $years = \Helper::listYears();

    $jenis_laporan = [
      'periode' => 'Per Periode', 
      'bulan' => 'Per Bulan', 
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

    return view('content.customer-service.laporan.laporan-posisi-perbaikan', [
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
    $base = DB::table('v_posisi_turun_lapangan as k')
    ->where('k.kode_cabang', $user_cabang);

    // Filtering (search global)
    $query = (clone $base);

    // Filter berdasarkan input yang dikirim dari DataTables
    if ($request->filled('jenis_laporan')) {
      if($request->jenis_laporan == "periode") {
        if ($request->filled('tgl_awal')) {
          $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('k.tgl_masuk', '>=', $startDate);
        }
        if ($request->filled('tgl_akhir')) {
          $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
          $query->whereDate('k.tgl_masuk', '<=', $endDate);
        }
      } elseif($request->jenis_laporan == "bulan") {
        if ($request->filled('bulan')) {
          $query->whereMonth('k.tgl_masuk', $request->bulan);
          $query->whereYear('k.tgl_masuk', $request->tahun2);
        }
      } elseif($request->jenis_laporan == "tahun") {
        if ($request->filled('tahun')) {
          $query->whereYear('k.tgl_masuk', $request->tahun);
        }
      }
    }

    // Total baris tanpa filter
    $totalData = (clone $query)->count('k.kode_spk');

    // Hitung setelah filter (tanpa limit/offset)
    $totalFiltered = (clone $query)->count('k.kode_spk');

    // Ambil data halaman saat ini
    $datas = $query
      ->select([
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.tgl_turun_lapangan',
        'k.tgl_rencana_selesai',
        'k.tgl_bongkar2',
        'k.tgl_las2',
        'k.tgl_dempul2',
        'k.tgl_mixing2',
        'k.tgl_cat2',
        'k.tgl_poles2',
        'k.tgl_finishing2',
        'k.nama_pelanggan',
      ])
      ->orderBy('k.tgl_masuk', 'asc')
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
          'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
          'tgl_rencana_selesai' => blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai)),
          'tgl_bongkar2' => blank($row->tgl_bongkar2) ? '' : date("d/m/Y", strtotime($row->tgl_bongkar2)),
          'tgl_las2' => blank($row->tgl_las2) ? '' : date("d/m/Y", strtotime($row->tgl_las2)),
          'tgl_dempul2' => blank($row->tgl_dempul2) ? '' : date("d/m/Y", strtotime($row->tgl_dempul2)),
          'tgl_mixing2' => blank($row->tgl_mixing2) ? '' : date("d/m/Y", strtotime($row->tgl_mixing2)),
          'tgl_cat2' => blank($row->tgl_cat2) ? '' : date("d/m/Y", strtotime($row->tgl_cat2)),
          'tgl_poles2' => blank($row->tgl_poles2) ? '' : date("d/m/Y", strtotime($row->tgl_poles2)),
          'tgl_finishing2' => blank($row->tgl_finishing2) ? '' : date("d/m/Y", strtotime($row->tgl_finishing2)),
          'nama_pelanggan' => $row->nama_pelanggan,
        ];
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
    $desc = "View Laporan Posisi Perbaikan";
    LogActivity::saveLogActivity($desc, $dataArray);

    return redirect('customer-service/laporan-posisi-perbaikan')->with('datafilter', $dataArray);
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
      $tglAkhir = $request->input('tgl_akhir', date('d/m/Y'));
      $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;
    } elseif($request->jenis_laporan == "bulan") {
      $months = \Helper::listMonths();
      $periodeStr = $months[$request->bulan] . ' ' . $request->tahun2;
    } elseif($request->jenis_laporan == "tahun") {
      $periodeStr = $request->tahun;
    }
    // ---------------------------------------

    $fileName = 'Laporan_Posisi_Perbaikan_' . date('Ymd_His') . '.xlsx';

    ## Log Activity
    $desc = "Export Laporan Posisi Perbaikan";
    LogActivity::saveLogActivity($desc, $filters);

    return Excel::download(new LaporanPosisiPerbaikanExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Posisi Perbaikan di Lapangan';

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    if($filters['jenis_laporan'] == "periode") {
      $tglAwal = date('d/m/Y', strtotime($filters['tgl_awal']));
      $tglAkhir = date('d/m/Y', strtotime($filters['tgl_akhir']));
      $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;
    } elseif($filters['jenis_laporan'] == "bulan") {
      $months = \Helper::listMonths();
      $periodeStr = $months[$filters['bulan']] . ' ' . $filters['tahun2'];
    } elseif($filters['jenis_laporan'] == "tahun") {
      $periodeStr = $filters['tahun'];
    }
    // ---------------------------------------

    // --- DATA ---
    $datas = [];
    $query = DB::table('v_posisi_turun_lapangan as k')
    ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
    ->select([
      'k.kode_spk',
      'k.no_polisi',
      'k.merek_tipe',
      'k.tgl_turun_lapangan',
      'k.tgl_rencana_selesai',
      'k.tgl_bongkar2',
      'k.tgl_las2',
      'k.tgl_dempul2',
      'k.tgl_mixing2',
      'k.tgl_cat2',
      'k.tgl_poles2',
      'k.tgl_finishing2',
      'k.nama_pelanggan',
    ])
    ->orderBy('k.tgl_masuk', 'asc');

    // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
    if (!empty($filters['jenis_laporan'])) {
        if($filters['jenis_laporan'] == "periode") {
            try {
                $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
                $query->whereDate('k.tgl_masuk', '>=', $startDate);
                $query->whereDate('k.tgl_masuk', '<=', $endDate);
            } catch (\Exception $e) {}
        } elseif($filters['jenis_laporan'] == "bulan") {
            $query->whereMonth('k.tgl_masuk', $filters['bulan']);
            $query->whereYear('k.tgl_masuk', $filters['tahun2']);
        } elseif($filters['jenis_laporan'] == "tahun") {
            $query->whereYear('k.tgl_masuk', $filters['tahun']);
        }
    }

    $datas = $query->get();
    $pages = 'content.customer-service.laporan.laporan-posisi-perbaikan-print';

    ## Log Activity
    $desc = "Print Laporan Posisi Perbaikan";
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