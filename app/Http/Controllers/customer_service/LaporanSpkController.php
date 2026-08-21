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
use App\Exports\LaporanSpkExport;    // EXPORT EXCEL

use App\Helpers\Helpers as Helper;

class LaporanSpkController extends Controller
{
  /**
   * Redirect to view.
   *
   */
  public function LaporanSPK(): View
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
      'spk' => 'SPK Master',
      'spk_tutup' => 'SPK Tutup',
      'spk_batal' => 'SPK Batal',
      'spk_keluar' => 'SPK Keluar'
    ];
    $jenis_laporan = [
      'periode' => 'Per Periode',
      'bulan' => 'Per Bulan',
      'tahun' => 'Per Tahun'
    ];

    // *** TAMBAHAN: ambil list customer dari v_rep_spk_master ***
    $customerList = DB::table('v_rep_spk_master')
      ->where('kode_cabang', $user_cabang)
      ->whereNotNull('pemilik')
      ->where('pemilik', '!=', '')
      ->select('pemilik')
      ->distinct()
      ->orderBy('pemilik', 'asc')
      ->pluck('pemilik');

    $datafilter = session('datafilter');
    if (empty($datafilter)) {
      $datafilter['tipe_laporan'] = '';
      $datafilter['jenis_laporan'] = 'periode';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
      $datafilter['bulan'] = date("m");
      $datafilter['tahun2'] = date("Y");
      $datafilter['tahun'] = date("Y");
      $datafilter['nama_customer'] = '';
    }

    return view('content.customer-service.laporan.laporan-spk', [
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
      'customerList' => $customerList, // *** TAMBAHAN ***
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

      if($request->tipe_laporan == "spk") {
        // Base query
        $base = DB::table('v_rep_spk_master as k')
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

        // *** filter customer / tertanggung (pemilik) ***
        if ($request->filled('nama_customer')) {
          $query->where('k.pemilik', 'like', '%' . $request->nama_customer . '%');
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
            'k.status',
            'k.status_spk',
            'k.merek_tipe',
            'k.pemilik',
            'k.telepon',
            'k.jenis_perbaikan',
            'k.nama_pelanggan',
            'k.tgl_estimasi',
            'k.kode_estimasi',
            'k.nilai_estimasi',
            'k.tgl_pengiriman',
            'k.tgl_turun_lapangan',
            'k.tgl_rencana_selesai',
            'k.tgl_keluar',
            'k.tanggal_or',
            'k.kode_or',
            'k.total_or',
            'k.tgl_invoice',
            'k.no_invoice',
            'k.nilai_tawar',
            'k.tgl_kwitansi',
            'k.kode_kwitansi',
            'k.nilai_kwitansi',
            'k.nama_surveyor',
            'k.nama_marketing',
            'k.nama_perantara',
          ])
          ->orderBy('k.tgl_masuk', 'asc')
          ->orderBy('k.kode_spk', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'kode_spk' => $row->kode_spk,
              'tgl_masuk' => blank($row->tgl_masuk) ? '' : date("d/m/Y", strtotime($row->tgl_masuk)),
              'no_polisi' => $row->no_polisi,
              'status' => $row->status,
              'status_spk' => $row->status_spk,
              'merek_tipe' => $row->merek_tipe,
              'pemilik' => $row->pemilik,
              'telepon' => $row->telepon,
              'jenis_perbaikan' => $row->jenis_perbaikan,
              'nama_pelanggan' => $row->nama_pelanggan,
              'tgl_estimasi' => blank($row->tgl_estimasi) ? '' : date("d/m/Y", strtotime($row->tgl_estimasi)),
              'kode_estimasi' => $row->kode_estimasi,
              'nilai_estimasi' => $row->nilai_estimasi,
              'tgl_pengiriman' => blank($row->tgl_pengiriman) ? '' : date("d/m/Y", strtotime($row->tgl_pengiriman)),
              'tgl_turun_lapangan' => blank($row->tgl_turun_lapangan) ? '' : date("d/m/Y", strtotime($row->tgl_turun_lapangan)),
              'tgl_rencana_selesai' => blank($row->tgl_rencana_selesai) ? '' : date("d/m/Y", strtotime($row->tgl_rencana_selesai)),
              'tgl_keluar' => blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)),
              'tanggal_or' => blank($row->tanggal_or) ? '' : date("d/m/Y", strtotime($row->tanggal_or)),
              'kode_or' => $row->kode_or,
              'total_or' => $row->total_or,
              'tgl_invoice' => blank($row->tgl_invoice) ? '' : date("d/m/Y", strtotime($row->tgl_invoice)),
              'no_invoice' => $row->no_invoice,
              'nilai_tawar' => $row->nilai_tawar,
              'tgl_kwitansi' => blank($row->tgl_kwitansi) ? '' : date("d/m/Y", strtotime($row->tgl_kwitansi)),
              'kode_kwitansi' => $row->kode_kwitansi,
              'nilai_kwitansi' => $row->nilai_kwitansi,
              'nama_surveyor' => $row->nama_surveyor,
              'nama_marketing' => $row->nama_marketing,
              'nama_perantara' => $row->nama_perantara,
            ];
        }
      } elseif($request->tipe_laporan == "spk_tutup") {
        // Base query
        $base = DB::table('v_rep_spk_tutup as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tanggal_tutup', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tanggal_tutup', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tanggal_tutup', $request->bulan);
              $query->whereYear('k.tanggal_tutup', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tanggal_tutup', $request->tahun);
            }
          }
        }

        // *** filter customer / tertanggung (pemilik) ***
        if ($request->filled('nama_customer')) {
          $query->where('k.pemilik', 'like', '%' . $request->nama_customer . '%');
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
              'k.kode_tutup',
              'k.tanggal_tutup',
              'k.kode_spk',
              'k.pemilik',
              'k.no_polisi',
              'k.merek_tipe',
          ])
          ->orderBy('k.tanggal_tutup', 'asc')
          ->orderBy('k.kode_tutup', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'kode_tutup' => $row->kode_tutup,
              'kode_spk' => $row->kode_spk,
              'pemilik' => $row->pemilik,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'tanggal_tutup' => blank($row->tanggal_tutup) ? '' : date("d/m/Y", strtotime($row->tanggal_tutup)),
            ];
        }
      } elseif($request->tipe_laporan == "spk_batal") {
        // Base query
        $base = DB::table('v_rep_spk_batal as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_batal', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_batal', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tgl_batal', $request->bulan);
              $query->whereYear('k.tgl_batal', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tgl_batal', $request->tahun);
            }
          }
        }

        // *** filter customer / tertanggung (pemilik) ***
        if ($request->filled('nama_customer')) {
          $query->where('k.pemilik', 'like', '%' . $request->nama_customer . '%');
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
              'k.tgl_batal',
              'k.kode_spk',
              'k.merek_tipe',
              'k.no_polisi',
              'k.nama_pelanggan',
              'k.pemilik',
              'k.batal_by',
              'k.memo_batal',
          ])
          ->orderBy('k.tgl_batal', 'asc')
          ->orderBy('k.kode_spk', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_batal' => blank($row->tgl_batal) ? '' : date("d/m/Y", strtotime($row->tgl_batal)),
              'kode_spk' => $row->kode_spk,
              'merek_tipe' => $row->merek_tipe,
              'no_polisi' => $row->no_polisi,
              'nama_pelanggan' => $row->nama_pelanggan,
              'pemilik' => $row->pemilik,
              'batal_by' => $row->batal_by,
              'memo_batal' => $row->memo_batal,
            ];
        }
      } elseif($request->tipe_laporan == "spk_keluar") {
        // Base query
        $base = DB::table('v_rep_spk_keluar as k')
          ->where('k.kode_cabang', $user_cabang);

        // Filtering (search global)
        $query = (clone $base);

        // Filter berdasarkan input yang dikirim dari DataTables
        if ($request->filled('jenis_laporan')) {
          if($request->jenis_laporan == "periode") {
            if ($request->filled('tgl_awal')) {
              $startDate = Carbon::createFromFormat('d/m/Y', $request->tgl_awal, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_keluar', '>=', $startDate);
            }
            if ($request->filled('tgl_akhir')) {
              $endDate = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir, 'Asia/Jakarta')->format('Y-m-d');
              $query->whereDate('k.tgl_keluar', '<=', $endDate);
            }
          } elseif($request->jenis_laporan == "bulan") {
            if ($request->filled('bulan')) {
              $query->whereMonth('k.tgl_keluar', $request->bulan);
              $query->whereYear('k.tgl_keluar', $request->tahun2);
            }
          } elseif($request->jenis_laporan == "tahun") {
            if ($request->filled('tahun')) {
              $query->whereYear('k.tgl_keluar', $request->tahun);
            }
          }
        }

        // *** filter customer / tertanggung (pemilik) ***
        if ($request->filled('nama_customer')) {
          $query->where('k.pemilik', 'like', '%' . $request->nama_customer . '%');
        }

        // Total baris tanpa filter
        $totalData = (clone $query)->count('k.id');

        // Hitung setelah filter (tanpa limit/offset)
        $totalFiltered = (clone $query)->count('k.id');

        // Ambil data halaman saat ini
        $datas = $query
          ->select([
              'k.tgl_keluar',
              'k.kode_keluar',
              'k.kode_spk',
              'k.no_polisi',
              'k.merek_tipe',
              'k.pemilik',
              'k.tgl_tanda_terima',
              'k.nama_penerima',
              'k.nama_pengantar',
          ])
          ->orderBy('k.tgl_keluar', 'asc')
          ->orderBy('k.kode_keluar', 'asc')
          ->get();

        // Susun payload DataTables
        $data = [];
        $fake = 0;
        foreach ($datas as $row) {
            $data[] = [
              'no' => ++$fake,
              'tgl_keluar' => blank($row->tgl_keluar) ? '' : date("d/m/Y", strtotime($row->tgl_keluar)),
              'kode_keluar' => $row->kode_keluar,
              'kode_spk' => $row->kode_spk,
              'no_polisi' => $row->no_polisi,
              'merek_tipe' => $row->merek_tipe,
              'pemilik' => $row->pemilik,
              'tgl_tanda_terima' => blank($row->tgl_tanda_terima) ? '' : date("d/m/Y", strtotime($row->tgl_tanda_terima)),
              'nama_penerima' => $row->nama_penerima,
              'nama_pengantar' => $row->nama_pengantar,
            ];
        }
      }

    }

    // ✅ Always return full DataTables structure, even if no results
    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData ?? 0),
      'recordsFiltered' => intval($totalFiltered ?? 0),
      'data' => $data ?? [],
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
      [
        'tipe_laporan.required'  => 'Laporan wajib diisi.',
        'jenis_laporan.required' => 'Jenis Laporan wajib diisi.',
      ]
    );

    $dataArray['tipe_laporan'] = $request->tipe_laporan;
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

    // *** simpan filter customer ke session ***
    $dataArray['nama_customer'] = $request->nama_customer ?? '';

    if($request->tipe_laporan == "spk") {
      $desc = "View Laporan SPK Master";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "spk_tutup") {
      $desc = "View Laporan SPK Tutup";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "spk_batal") {
      $desc = "View Laporan SPK Batal";
      LogActivity::saveLogActivity($desc, $dataArray);
    } elseif($request->tipe_laporan == "spk_keluar") {
      $desc = "View Laporan SPK Keluar";
      LogActivity::saveLogActivity($desc, $dataArray);
    }

    return redirect('customer-service/laporan-spk')->with('datafilter', $dataArray);
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

    if($request->tipe_laporan == "spk") {
      $fileName = 'Laporan_SPK_Master_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "spk_tutup") {
      $fileName = 'Laporan_SPK_Tutup_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "spk_batal") {
      $fileName = 'Laporan_SPK_Batal_' . date('Ymd_His') . '.xlsx';
    } elseif($request->tipe_laporan == "spk_keluar") {
      $fileName = 'Laporan_SPK_Keluar_' . date('Ymd_His') . '.xlsx';
    }

    if($request->tipe_laporan == "spk") {
      $desc = "Export Laporan SPK Master";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "spk_tutup") {
      $desc = "Export Laporan SPK Tutup";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "spk_batal") {
      $desc = "Export Laporan SPK Batal";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "spk_keluar") {
      $desc = "Export Laporan SPK Keluar";
      LogActivity::saveLogActivity($desc, $filters);
    }

    return Excel::download(new LaporanSpkExport($filters, $cabangData, $periodeStr ?? ''), $fileName ?? 'Laporan_SPK.xlsx');
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan SPK';

    $filters = $request->all();

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

    $datas = [];
    if($filters['tipe_laporan'] == "spk") {
      $query = DB::table('v_rep_spk_master as k')
        ->where('k.kode_cabang', $user_cabang)
        ->select([
          'k.id', 'k.kode_spk', 'k.tgl_masuk', 'k.no_polisi', 'k.status',
          'k.status_spk', 'k.merek_tipe', 'k.pemilik', 'k.telepon',
          'k.jenis_perbaikan', 'k.nama_pelanggan', 'k.tgl_estimasi',
          'k.kode_estimasi', 'k.nilai_estimasi', 'k.tgl_pengiriman',
          'k.tgl_turun_lapangan', 'k.tgl_rencana_selesai', 'k.tgl_keluar',
          'k.tanggal_or', 'k.kode_or', 'k.total_or', 'k.tgl_invoice',
          'k.no_invoice', 'k.nilai_tawar', 'k.tgl_kwitansi', 'k.kode_kwitansi',
          'k.nilai_kwitansi', 'k.nama_surveyor', 'k.nama_marketing', 'k.nama_perantara',
        ])
        ->orderBy('k.tgl_masuk', 'asc')
        ->orderBy('k.kode_spk', 'asc');

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

      // *** filter customer ***
      if (!empty($filters['nama_customer'])) {
        $query->where('k.pemilik', 'like', '%' . $filters['nama_customer'] . '%');
      }

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-spk-master-print';

    } elseif($filters['tipe_laporan'] == "spk_tutup") {
      $query = DB::table('v_rep_spk_tutup as k')
        ->where('k.kode_cabang', $user_cabang)
        ->select(['k.kode_tutup', 'k.tanggal_tutup', 'k.kode_spk', 'k.pemilik', 'k.no_polisi', 'k.merek_tipe'])
        ->orderBy('k.tanggal_tutup', 'asc')
        ->orderBy('k.kode_tutup', 'asc');

      if (!empty($filters['jenis_laporan'])) {
        if($filters['jenis_laporan'] == "periode") {
          try {
            $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
            $query->whereDate('k.tanggal_tutup', '>=', $startDate);
            $query->whereDate('k.tanggal_tutup', '<=', $endDate);
          } catch (\Exception $e) {}
        } elseif($filters['jenis_laporan'] == "bulan") {
          $query->whereMonth('k.tanggal_tutup', $filters['bulan']);
          $query->whereYear('k.tanggal_tutup', $filters['tahun2']);
        } elseif($filters['jenis_laporan'] == "tahun") {
          $query->whereYear('k.tanggal_tutup', $filters['tahun']);
        }
      }

      // *** filter customer ***
      if (!empty($filters['nama_customer'])) {
        $query->where('k.pemilik', 'like', '%' . $filters['nama_customer'] . '%');
      }

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-spk-tutup-print';

    } elseif($filters['tipe_laporan'] == "spk_batal") {
      $query = DB::table('v_rep_spk_batal as k')
        ->where('k.kode_cabang', $user_cabang)
        ->select(['k.tgl_batal', 'k.kode_spk', 'k.merek_tipe', 'k.no_polisi', 'k.nama_pelanggan', 'k.pemilik', 'k.batal_by', 'k.memo_batal'])
        ->orderBy('k.tgl_batal', 'asc')
        ->orderBy('k.kode_spk', 'asc');

      if (!empty($filters['jenis_laporan'])) {
        if($filters['jenis_laporan'] == "periode") {
          try {
            $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
            $query->whereDate('k.tgl_batal', '>=', $startDate);
            $query->whereDate('k.tgl_batal', '<=', $endDate);
          } catch (\Exception $e) {}
        } elseif($filters['jenis_laporan'] == "bulan") {
          $query->whereMonth('k.tgl_batal', $filters['bulan']);
          $query->whereYear('k.tgl_batal', $filters['tahun2']);
        } elseif($filters['jenis_laporan'] == "tahun") {
          $query->whereYear('k.tgl_batal', $filters['tahun']);
        }
      }

      // *** filter customer ***
      if (!empty($filters['nama_customer'])) {
        $query->where('k.pemilik', 'like', '%' . $filters['nama_customer'] . '%');
      }

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-spk-batal-print';

    } elseif($filters['tipe_laporan'] == "spk_keluar") {
      $query = DB::table('v_rep_spk_keluar as k')
        ->where('k.kode_cabang', $user_cabang)
        ->select(['k.tgl_keluar', 'k.kode_keluar', 'k.kode_spk', 'k.no_polisi', 'k.merek_tipe', 'k.pemilik', 'k.tgl_tanda_terima', 'k.nama_penerima', 'k.nama_pengantar'])
        ->orderBy('k.tgl_keluar', 'asc')
        ->orderBy('k.kode_keluar', 'asc');

      if (!empty($filters['jenis_laporan'])) {
        if($filters['jenis_laporan'] == "periode") {
          try {
            $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
            $query->whereDate('k.tgl_keluar', '>=', $startDate);
            $query->whereDate('k.tgl_keluar', '<=', $endDate);
          } catch (\Exception $e) {}
        } elseif($filters['jenis_laporan'] == "bulan") {
          $query->whereMonth('k.tgl_keluar', $filters['bulan']);
          $query->whereYear('k.tgl_keluar', $filters['tahun2']);
        } elseif($filters['jenis_laporan'] == "tahun") {
          $query->whereYear('k.tgl_keluar', $filters['tahun']);
        }
      }

      // *** filter customer ***
      if (!empty($filters['nama_customer'])) {
        $query->where('k.pemilik', 'like', '%' . $filters['nama_customer'] . '%');
      }

      $datas = $query->get();
      $pages = 'content.customer-service.laporan.laporan-spk-keluar-print';
    }

    if($request->tipe_laporan == "spk") {
      $desc = "Print Laporan SPK Master";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "spk_tutup") {
      $desc = "Print Laporan SPK Tutup";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "spk_batal") {
      $desc = "Print Laporan SPK Batal";
      LogActivity::saveLogActivity($desc, $filters);
    } elseif($request->tipe_laporan == "spk_keluar") {
      $desc = "Print Laporan SPK Keluar";
      LogActivity::saveLogActivity($desc, $filters);
    }

    $pageConfigs = ['myLayout' => 'blank'];
    return view($pages, [
      'title' => $title,
      'namaCabang' => $namaCabang,
      'periodeStr' => $periodeStr ?? '',
      'no' => 1,
      'datas' => $datas,
      'pageConfigs' => $pageConfigs,
    ]);
  }

}
