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
use App\Exports\LaporanKendaraanExport;    // EXPORT EXCEL
use App\Exports\LaporanMobilMasukExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class LaporanKendaraanController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanKendaraan(): View
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
    $tipe_laporan = [
      'mobil_masuk' => 'Mobil Masuk', 
      'mobil_belum_turun' => 'Mobil Belum Turun Lapangan', 
      'mobil_turun' => 'Mobil Turun Lapangan', 
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

    $dataMobilMasuk = [];
    if($datafilter['tipe_laporan'] == "mobil_masuk") {
      $dataMobilMasuk = $this->getRepMobilMasuk($datafilter);
    }

    return view('content.customer-service.laporan.laporan-kendaraan', [
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
      'dataMobilMasuk' => $dataMobilMasuk,
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

    $totalData = 0;
    $totalFiltered = 0;
    $data = [];

    if ($request->filled('tipe_laporan')) {

      if($request->tipe_laporan == "mobil_masuk") {
        // Base query
        $base = DB::table('v_rep_mobil_masuk as k')
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
            'k.kode_spk',
            'k.tgl_masuk',
            'k.no_polisi',
            'k.tipe_kendaraan',
            'k.nama_pelanggan',
          ])
          ->orderBy('k.tgl_masuk', 'asc')
          ->orderBy('k.kode_spk', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'kode_spk' => $row->kode_spk,
              'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
              'no_polisi' => $row->no_polisi,
              'tipe_kendaraan' => $row->tipe_kendaraan,
              'nama_pelanggan' => $row->nama_pelanggan,
            ];
        }
      } elseif($request->tipe_laporan == "mobil_belum_turun") {
        // Base query
        $base = DB::table('v_rep_belum_turun_lapangan as k')
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
            'k.tgl_masuk',
            'k.kode_spk',
            'k.no_polisi',
            'k.merek_tipe',
            'k.nama_pelanggan',
            'k.pemilik',
          ])
          ->orderBy('k.tgl_masuk', 'asc')
          ->orderBy('k.kode_spk', 'asc')
          // ->offset($start)
          // ->limit($limit)
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
              'kode_spk' => $row->kode_spk,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'nama_pelanggan' => $row->nama_pelanggan,
              'pemilik' => $row->pemilik,
              
            ];
        }
      } elseif($request->tipe_laporan == "mobil_turun") {
        // Base query
        $base = DB::table('v_rep_turun_lapangan as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_turun_lapangan', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_turun_lapangan', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tgl_turun_lapangan', $request->bulan);
              $query->whereYear('k.tgl_turun_lapangan', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tgl_turun_lapangan', $request->tahun);
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
              'k.tgl_turun_lapangan',
              'k.kode_turun_lapangan',
              'k.kode_spk',
              'k.no_polisi',
              'k.merek_tipe',
              'k.nama_pelanggan',
              'k.pemilik',
              'k.tgl_rencana_selesai',
              'k.status',
          ])
          // ->orderBy('k.tgl_turun_lapangan', 'asc')
          ->orderBy('k.kode_turun_lapangan', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0; //$start;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
              'kode_turun_lapangan' => $row->kode_turun_lapangan,
              'kode_spk' => $row->kode_spk,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'nama_pelanggan' => $row->nama_pelanggan,
              'pemilik' => $row->pemilik,
              'tgl_rencana_selesai' => blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai)),
              'status' => $row->status,
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

    if($request->tipe_laporan == "mobil_masuk") {
      ## Log Activity
      $desc = "View Laporan Mobil Masuk";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "mobil_belum_turun") {
      ## Log Activity
      $desc = "View Laporan Mobil Belum Turun Lapangan";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "mobil_turun") {
      ## Log Activity
      $desc = "View Laporan Mobil Turun Lapangan";
      LogActivity::saveLogActivity($desc, $dataArray);
    }

    return redirect('customer-service/laporan-kendaraan')->with('datafilter', $dataArray);
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
    $periodeStr = '';
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

    $fileName = '';
    if($request->tipe_laporan == "mobil_masuk") {
      $fileName = 'Laporan_Mobil_Masuk_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "mobil_belum_turun") {
      $fileName = 'Laporan_Mobil_Belum_Turun_Lapangan_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "mobil_turun") {
      $fileName = 'Laporan_Mobil_Turun_Lapangan_' . date('Ymd_His') . '.xlsx';
    }

    if($request->tipe_laporan == "mobil_masuk") {
      ## Log Activity
      $desc = "Export Laporan Mobil Masuk";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "mobil_belum_turun") {
      ## Log Activity
      $desc = "Export Laporan Mobil Belum Turun Lapangan";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "mobil_turun") {
      ## Log Activity
      $desc = "Export Laporan Mobil Turun Lapangan";
      LogActivity::saveLogActivity($desc, $filters);
    }

    // Masukkan $periodeStr dan $cabangData ke dalam use App\Helpers\Helpers as Helper;

    if($request->tipe_laporan == "mobil_masuk") {
      return Excel::download(new LaporanMobilMasukExport($filters, $cabangData, $periodeStr), $fileName);
    } else {
      return Excel::download(new LaporanKendaraanExport($filters, $cabangData, $periodeStr), $fileName);
    }
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
    $periodeStr = '';
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
    $pages = '';
    if($filters['tipe_laporan'] == "mobil_masuk") {
      $datas = $this->getRepMobilMasuk($filters);
      $pages = 'content.customer-service.laporan.laporan-kendaraan-masuk-print';
    } elseif($filters['tipe_laporan'] == "mobil_belum_turun") {
      $query = DB::table('v_rep_belum_turun_lapangan as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.tgl_masuk',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.pemilik',
      ])
      ->orderBy('k.tgl_masuk', 'asc')
      ->orderBy('k.kode_spk', 'asc');

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
      $pages = 'content.customer-service.laporan.laporan-kendaraan-belum-turun-lapangan-print';
    } elseif($filters['tipe_laporan'] == "mobil_turun") {
      $query = DB::table('v_rep_turun_lapangan as k')
      ->where('k.kode_cabang', $user_cabang) // Sesuaikan jika ini array/string
      ->select([
        'k.tgl_turun_lapangan',
        'k.kode_turun_lapangan',
        'k.kode_spk',
        'k.no_polisi',
        'k.merek_tipe',
        'k.nama_pelanggan',
        'k.pemilik',
        'k.tgl_rencana_selesai',
        'k.status',
      ])
      ->orderBy('k.tgl_turun_lapangan', 'asc')
      ->orderBy('k.kode_turun_lapangan', 'asc');

      // --- FILTERING LOGIC (Sama seperti sebelumnya) ---
      if (!empty($filters['jenis_laporan'])) {
          if($filters['jenis_laporan'] == "periode") {
              try {
                  $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
                  $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
                  $query->whereDate('k.tgl_turun_lapangan', '>=', $startDate);
                  $query->whereDate('k.tgl_turun_lapangan', '<=', $endDate);
              } catch (\Exception $e) {}
          } elseif($filters['jenis_laporan'] == "bulan") {
              $query->whereMonth('k.tgl_turun_lapangan', $filters['bulan']);
              $query->whereYear('k.tgl_turun_lapangan', $filters['tahun2']);
          } elseif($filters['jenis_laporan'] == "tahun") {
              $query->whereYear('k.tgl_turun_lapangan', $filters['tahun']);
          }
      }

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-kendaraan-turun-lapangan-print';
    }

    if($request->tipe_laporan == "mobil_masuk") {
      ## Log Activity
      $desc = "Print Laporan Mobil Masuk";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "mobil_belum_turun") {
      ## Log Activity
      $desc = "Print Laporan Mobil Belum Turun Lapangan";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "mobil_turun") {
      ## Log Activity
      $desc = "Print Laporan Mobil Turun Lapangan";
      LogActivity::saveLogActivity($desc, $filters);
    }

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

  private function getRepMobilMasuk(array $param)
  {
    $user_cabang = session('kd_cabang');

    // Base query
    $base = DB::table('v_rep_mobil_masuk as k')
      ->where('k.kode_cabang', $user_cabang);

    // Filtering (search global)
    $query = (clone $base);

    // Filter berdasarkan input yang dikirim dari DataTables
    if($param['jenis_laporan'] == "periode") {
      if (strlen($param['tgl_awal'])) {
        $startDate = Carbon::createFromFormat('d/m/Y', $param['tgl_awal'], 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '>=', $startDate);
      }
      if (strlen($param['tgl_akhir'])) {
        $endDate = Carbon::createFromFormat('d/m/Y', $param['tgl_akhir'], 'Asia/Jakarta')->format('Y-m-d');
        $query->whereDate('k.tgl_masuk', '<=', $endDate);
      }
    } elseif($param['jenis_laporan'] == "bulan") {
      if (strlen($param['tgl_awal'])) {
        $query->whereMonth('k.tgl_masuk', $param['bulan']);
        $query->whereYear('k.tgl_masuk', $param['tahun2']);
      }
    } elseif($param['jenis_laporan'] == "tahun") {
      if (strlen($param['tgl_awal'])) {
        $query->whereYear('k.tgl_masuk', $param['tahun']);
      }
    }


    // Ambil data halaman saat ini
    if($param['jenis_laporan'] == "tahun") {
      $datas = $query
      ->select([
        'k.nama_pelanggan',
        DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-01') as tgl_masuk"),
        DB::raw("count(1) as jum")
      ])
      ->groupBy([
        DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-01')"),
        'k.nama_pelanggan'
      ])
      ->orderBy('k.nama_pelanggan', 'asc')
      ->orderBy('k.tgl_masuk', 'asc')
      ->get();
    } else {
      $datas = $query
      ->select([
        'k.nama_pelanggan',
        DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-%d') as tgl_masuk"),
        DB::raw("count(1) as jum")
      ])
      ->groupBy([
        DB::raw("DATE_FORMAT(k.tgl_masuk,'%Y-%m-%d')"),
        'k.nama_pelanggan'
      ])
      ->orderBy('k.nama_pelanggan', 'asc')
      ->orderBy('k.tgl_masuk', 'asc')
      ->get();
    }
    

    // Susun payload DataTables
    $tmp = [];
    $fields = [];
    $total = [];
    $data = [];
    foreach ($datas as $row) {
      if($param['jenis_laporan'] == "tahun") {
        $tgl = blank($row->tgl_masuk) ? '' : date("Ym01", strtotime($row->tgl_masuk));
      } else {
        $tgl = blank($row->tgl_masuk) ? '' : date("Ymd", strtotime($row->tgl_masuk));
      }
      $fields[$tgl] = $tgl;
      $tmp[$row->nama_pelanggan][$tgl] = $row->jum;
      if(isset($total[$tgl])) {
        $total[$tgl] += $row->jum;
      } else {
        $total[$tgl] = $row->jum;
      }
    }

    if(is_array($tmp)) {
      ksort($fields);
      ksort($total);
      $data['fields'] = $fields;
      $data['total'] = $total;
      $data['data'] = @$tmp;
    }    

    return $data;
  }
}