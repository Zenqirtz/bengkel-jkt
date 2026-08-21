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
use App\Exports\LaporanInvoiceExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class LaporanInvoiceController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanInvoice(): View
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
    $title = Helper::getTitleMenu($path) ?? 'SPK';

    $user_cabang = session('kd_cabang');

    $months = Helper::listMonths();
    $years = Helper::listYears();
    $tipe_laporan = [
      'inv_belum_terbit' => 'Invoice OR Belum Terbit', 
      'inv_terbit' => 'Invoice OR Terbit', 
      'inv_belum_tagih' => 'Invoice OR Belum Ditagih', 
      'inv_belum_lunas' => 'Invoice OR Belum Lunas', 
    ];
    $jenis_laporan = [
      'periode' => 'Per Periode', 
      'bulan' => 'Per Bulan', 
      'tahun' => 'Per Tahun'
    ];

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tipe_laporan'] = '';
      $datafilter['jenis_laporan'] = 'periode';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
      $datafilter['bulan'] = date("m");
      $datafilter['tahun2'] = date("Y");
      $datafilter['tahun'] = date("Y");
    }

    return view('content.customer-service.laporan.laporan-invoice', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'months' => $months,
      'years' => $years,
      'tipe_laporan' => $tipe_laporan,
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

    if ($request->filled('tipe_laporan')) {

      if($request->tipe_laporan == "inv_belum_terbit") {
        // Base query
        $base = DB::table('v_rpt_belum_ada_or as k')
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
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.id',
            'k.tgl_masuk',
            'k.kode_spk',
            'k.no_polisi',
            'k.merek_tipe',
            'k.pemilik',
            'k.nama_pelanggan',
            'k.status_spk',
          ])
          ->orderBy('k.tgl_masuk', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
              'kode_spk' => $row->kode_spk,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'pemilik' => $row->pemilik,
              'nama_pelanggan' => $row->nama_pelanggan,
              'status_spk' => $row->status_spk,
            ];
        }
      } elseif($request->tipe_laporan == "inv_terbit") {
        // Base query
        $base = DB::table('v_rpt_terbit_kwitansi_or as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_invoice', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_invoice', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tgl_invoice', $request->bulan);
              $query->whereYear('k.tgl_invoice', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tgl_invoice', $request->tahun);
            }
          }
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.tgl_invoice',
            'k.no_invoice',
            'k.kode_spk',
            'k.tertanggung',
            'k.no_polisi',
            'k.merek_tipe',
            'k.nama_pelanggan',
            'k.total_or',
          ])
          ->orderBy('k.tgl_invoice', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_invoice' => blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)),
              'no_invoice' => $row->no_invoice,
              'kode_spk' => $row->kode_spk,
              'tertanggung' => $row->tertanggung,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'nama_pelanggan' => $row->nama_pelanggan,
              'total_or' => number_format($row->total_or,0,".",","),
            ];
        }
      } elseif($request->tipe_laporan == "inv_belum_tagih") {
        // Base query
        $base = DB::table('v_rep_kwt_or_belum_ditagih as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_invoice', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_invoice', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tgl_invoice', $request->bulan);
              $query->whereYear('k.tgl_invoice', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tgl_invoice', $request->tahun);
            }
          }
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.tgl_invoice',
            'k.no_invoice',
            'k.kode_spk',
            'k.tertanggung',
            'k.no_polisi',
            'k.merek_tipe',
            'k.nama_pelanggan',
            'k.total_or',
          ])
          ->orderBy('k.tgl_invoice', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_invoice' => blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)),
              'no_invoice' => $row->no_invoice,
              'kode_spk' => $row->kode_spk,
              'tertanggung' => $row->tertanggung,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'nama_pelanggan' => $row->nama_pelanggan,
              'total_or' => number_format($row->total_or,0,".",","),
            ];
        }
      } elseif($request->tipe_laporan == "inv_belum_lunas") {
        // Base query
        $base = DB::table('v_rpt_or_belum_lunas as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_invoice', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_invoice', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tgl_invoice', $request->bulan);
              $query->whereYear('k.tgl_invoice', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tgl_invoice', $request->tahun);
            }
          }
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
            'k.tgl_invoice',
            'k.no_invoice',
            'k.kode_spk',
            'k.tertanggung',
            'k.no_polisi',
            'k.merek_tipe',
            'k.nama_pelanggan',
            'k.total_or',
            'k.kode_keluar',
            'k.keterangan',
          ])
          ->orderBy('k.tgl_invoice', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_invoice' => blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)),
              'no_invoice' => $row->no_invoice,
              'kode_spk' => $row->kode_spk,
              'tertanggung' => $row->tertanggung,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'nama_pelanggan' => $row->nama_pelanggan,
              'total_or' => number_format($row->total_or,0,".",","),
              'kode_keluar' => $row->kode_keluar,
              'keterangan' => $row->keterangan,
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
        'tipe_laporan' => 'required',
        'jenis_laporan' => 'required'
      ],
      [ // custom messages
        'tipe_laporan.required'    => 'Laporan wajib diisi.',
        'jenis_laporan.required'    => 'Jenis Laporan wajib diisi.',
      ]
    );

    $dataArray['tipe_laporan'] = $request->tipe_laporan;
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

    if($request->tipe_laporan == "inv_belum_terbit") {
      ## Log Activity
      $desc = "View Laporan Invoice OR Belum Terbit";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "inv_terbit") {
      ## Log Activity
      $desc = "View Laporan Invoice OR Terbit";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "inv_belum_tagih") {
      ## Log Activity
      $desc = "View Laporan Invoice OR Belum Ditagih";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "inv_belum_lunas") {
      ## Log Activity
      $desc = "View Laporan Invoice OR Belum Lunas";
      LogActivity::saveLogActivity($desc, $dataArray);
    }

    return redirect('customer-service/laporan-invoice')->with('datafilter', $dataArray);
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
      $months = Helper::listMonths();
      $periodeStr = $months[$request->bulan] . ' ' . $request->tahun2;
    } elseif($request->jenis_laporan == "tahun") {
      $periodeStr = $request->tahun;
    }
    // ---------------------------------------

    if($request->tipe_laporan == "inv_belum_terbit") {
      $fileName = 'Laporan_Invoice_OR_Belum_Terbit_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "inv_terbit") {
      $fileName = 'Laporan_Invoice_OR_Terbit_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "inv_belum_tagih") {
      $fileName = 'Laporan_Invoice_OR_Belum_Ditagih_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "inv_belum_lunas") {
      $fileName = 'Laporan_Invoice_OR_Belum_Lunas_' . date('Ymd_His') . '.xlsx';
    }

    if($request->tipe_laporan == "inv_belum_terbit") {
      ## Log Activity
      $desc = "Export Laporan Invoice OR Belum Terbit";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "inv_terbit") {
      ## Log Activity
      $desc = "Export Laporan Invoice OR Terbit";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "inv_belum_tagih") {
      ## Log Activity
      $desc = "Export Laporan Invoice OR Belum Ditagih";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "inv_belum_lunas") {
      ## Log Activity
      $desc = "Export Laporan Invoice OR Belum Lunas";
      LogActivity::saveLogActivity($desc, $filters);
    }

    // Masukkan $periodeStr dan $cabangData ke dalam use App\Helpers\Helpers as Helper;

class Export
    return Excel::download(new LaporanInvoiceExport($filters, $cabangData, $periodeStr), $fileName);
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Invoice OR Belum Terbit';

    $filters = $request->all();

    // --- TAMBAHAN: Format String Periode ---
    if($filters['jenis_laporan'] == "periode") {
      $tglAwal = date('d/m/Y', strtotime($filters['tgl_awal']));
      $tglAkhir = date('d/m/Y', strtotime($filters['tgl_akhir']));
      $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;
    } elseif($filters['jenis_laporan'] == "bulan") {
      $months = Helper::listMonths();
      $periodeStr = $months[$filters['bulan']] . ' ' . $filters['tahun2'];
    } elseif($filters['jenis_laporan'] == "tahun") {
      $periodeStr = $filters['tahun'];
    }
    // ---------------------------------------

    // --- DATA ---
    $datas = [];
    if($filters['tipe_laporan'] == "inv_belum_terbit") {
      $query = DB::table('v_rpt_belum_ada_or as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.id',
        'k.tgl_masuk',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.pemilik',
        'k.nama_pelanggan',
        'k.status_spk',
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
      $pages = 'content.customer-service.laporan.laporan-inv-belum-terbit-print';
    } elseif($filters['tipe_laporan'] == "inv_terbit") {
      $query = DB::table('v_rpt_terbit_kwitansi_or as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.tgl_invoice',
        'k.no_invoice',
        'k.kode_spk',
        'k.tertanggung',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.total_or',
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
      $pages = 'content.customer-service.laporan.laporan-inv-terbit-print';
    } elseif($filters['tipe_laporan'] == "inv_belum_tagih") {
      $query = DB::table('v_rep_kwt_or_belum_ditagih as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.tgl_invoice',
        'k.no_invoice',
        'k.kode_spk',
        'k.tertanggung',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.total_or',
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
      $pages = 'content.customer-service.laporan.laporan-inv-belum-tagih-print';
    } elseif($filters['tipe_laporan'] == "inv_belum_lunas") {
      $query = DB::table('v_rpt_or_belum_lunas as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.tgl_invoice',
        'k.no_invoice',
        'k.kode_spk',
        'k.tertanggung',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.total_or',
        'k.kode_keluar',
        'k.keterangan',
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
      $pages = 'content.customer-service.laporan.laporan-inv-belum-lunas-print';
    }

    if($request->tipe_laporan == "inv_belum_terbit") {
      ## Log Activity
      $desc = "Print Laporan Invoice OR Belum Terbit";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "inv_terbit") {
      ## Log Activity
      $desc = "Print Laporan Invoice OR Terbit";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "inv_belum_tagih") {
      ## Log Activity
      $desc = "Print Laporan Invoice OR Belum Ditagih";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "inv_belum_lunas") {
      ## Log Activity
      $desc = "Print Laporan Invoice OR Belum Lunas";
      LogActivity::saveLogActivity($desc, $filters);
    }

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr,
      'no' => 1,
      'datas' => $datas,
      'pageConfigs' => $pageConfigs,
    ]);
  }

}