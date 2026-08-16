<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPengeluaranBahanExport;

class LaporanPengeluaranBahanController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanPengeluaranBahan(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Laporan Pengeluaran Bahan';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_pengeluaran_bahan');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.gudang.laporan.laporan-pengeluaran-bahan', [
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
  // public function index(Request $request): JsonResponse
  // {
  //   $user_cabang = session('kd_cabang');

  //   $tgl_awal = $request->filled('tgl_awal')
  //     ? $request->tgl_awal
  //     : session('datafilter_pengeluaran_bahan.tgl_awal', date("d/m/Y"));

  //   $tgl_akhir = $request->filled('tgl_akhir')
  //     ? $request->tgl_akhir
  //     : session('datafilter_pengeluaran_bahan.tgl_akhir', date("d/m/Y"));

  //   $query = DB::table('v_rep_pengeluaran_bahan as k')
  //     ->where('k.kode_cabang', $user_cabang);

  //   // Filter tanggal
  //   if (!empty($tgl_awal)) {
  //     try {
  //       $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
  //       $query->whereDate('k.tgl_pengeluaran', '>=', $startDate);
  //     } catch (\Exception $e) {
  //       // Handle error
  //     }
  //   }

  //   if (!empty($tgl_akhir)) {
  //     try {
  //       $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
  //       $query->whereDate('k.tgl_pengeluaran', '<=', $endDate);
  //     } catch (\Exception $e) {
  //       // Handle error
  //     }
  //   }

  //   $totalData = (clone $query)->count();
  //   $totalFiltered = (clone $query)->count();

  //   $datas = $query
  //     ->select([
  //       'k.kode_pengeluaran',
  //       'k.tgl_pengeluaran',
  //       'k.no_bon',
  //       'k.kode_spk',
  //       'k.no_polisi',
  //       'k.posisi_pekerjaan',
  //       'k.point_panel',
  //       'k.kode_barang',
  //       'k.nama_bahan',
  //       'k.qty',
  //       'k.satuan',
  //       'k.harga_lama',
  //       'k.harga',
  //       'k.jumlah'
  //     ])
  //     ->orderBy('k.nama_bahan', 'asc')
  //     ->orderBy('k.tgl_pengeluaran', 'asc')
  //     ->orderBy('k.kode_pengeluaran', 'asc')
  //     ->get();

  //   $data = [];

  //   // Hanya proses jika ada data
  //   if (count($datas) > 0) {
  //     $no = 0;
  //     $currentBahan = null;
  //     $subtotal = [
  //       'qty' => 0,
  //       'harga_lama' => 0,
  //       'harga' => 0,
  //       'jumlah' => 0,
  //       'satuan' => ''
  //     ];

  //     foreach ($datas as $index => $row) {
  //       // Jika nama bahan berubah dan bukan data pertama, tambahkan subtotal
  //       if ($currentBahan !== null && $currentBahan !== $row->nama_bahan) {
  //         // Harga Lama berisi penjumlahan harga_lama + harga
  //         $totalHarga = $subtotal['harga_lama'] + $subtotal['harga'];

  //         $data[] = [
  //           'no' => '',
  //           'kode_pengeluaran' => '',
  //           'tgl_pengeluaran' => '',
  //           'no_bon' => '',
  //           'kode_spk' => '',
  //           'no_polisi' => '',
  //           'posisi_pekerjaan' => '',
  //           'point_panel' => '',
  //           'kode_barang' => '',
  //           'nama_bahan' => 'Total', // Nama Bahan berisi "Total"
  //           'qty' => number_format($subtotal['qty'], 2, '.', '.'),
  //           'satuan' => $subtotal['satuan'], // Satuan tetap satuan
  //           'harga_lama' => number_format($totalHarga, 0, '.', '.'), // Penjumlahan harga_lama + harga
  //           'harga' => '', // Kosong (merged)
  //           'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
  //           'is_subtotal' => true
  //         ];

  //         // Reset subtotal
  //         $subtotal = [
  //           'qty' => 0,
  //           'harga_lama' => 0,
  //           'harga' => 0,
  //           'jumlah' => 0,
  //           'satuan' => ''
  //         ];
  //       }

  //       $currentBahan = $row->nama_bahan;

  //       // Tambah ke subtotal
  //       $subtotal['qty'] += $row->qty;
  //       $subtotal['harga_lama'] += $row->harga_lama;
  //       $subtotal['harga'] += $row->harga;
  //       $subtotal['jumlah'] += $row->jumlah;
  //       $subtotal['satuan'] = $row->satuan; // Simpan satuan

  //       $data[] = [
  //         'no' => ++$no,
  //         'kode_pengeluaran' => $row->kode_pengeluaran,
  //         'tgl_pengeluaran' => Carbon::parse($row->tgl_pengeluaran)->format('d/m/Y'),
  //         'no_bon' => $row->no_bon ?? '',
  //         'kode_spk' => $row->kode_spk ?? '',
  //         'no_polisi' => $row->no_polisi ?? '',
  //         'posisi_pekerjaan' => $row->posisi_pekerjaan ?? '',
  //         'point_panel' => $row->point_panel ?? '',
  //         'kode_barang' => $row->kode_barang,
  //         'nama_bahan' => $row->nama_bahan,
  //         'qty' => number_format($row->qty, 2, '.', '.'),
  //         'satuan' => $row->satuan ?? '',
  //         'harga_lama' => number_format($row->harga_lama, 0, '.', '.'),
  //         'harga' => number_format($row->harga, 0, '.', '.'),
  //         'jumlah' => number_format($row->jumlah, 0, '.', '.'),
  //         'is_subtotal' => false
  //       ];
  //     }

  //     // Tambahkan subtotal terakhir (hanya jika ada data)
  //     $totalHarga = $subtotal['harga_lama'] + $subtotal['harga'];

  //     $data[] = [
  //       'no' => '',
  //       'kode_pengeluaran' => '',
  //       'tgl_pengeluaran' => '',
  //       'no_bon' => '',
  //       'kode_spk' => '',
  //       'no_polisi' => '',
  //       'posisi_pekerjaan' => '',
  //       'point_panel' => '',
  //       'kode_barang' => '',
  //       'nama_bahan' => 'Total', // Nama Bahan berisi "Total"
  //       'qty' => number_format($subtotal['qty'], 2, '.', '.'),
  //       'satuan' => $subtotal['satuan'], // Satuan tetap satuan
  //       'harga_lama' => number_format($totalHarga, 0, '.', '.'), // Penjumlahan harga_lama + harga
  //       'harga' => '', // Kosong (merged)
  //       'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
  //       'is_subtotal' => true
  //     ];
  //   }

  //   return response()->json([
  //     'draw' => intval($request->input('draw')),
  //     'recordsTotal' => intval($totalData),
  //     'recordsFiltered' => intval($totalFiltered),
  //     'data' => $data,
  //   ]);
  // }
  public function index(Request $request): JsonResponse
  {
    $user_cabang = session('kd_cabang');

    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter_pengeluaran_bahan.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter_pengeluaran_bahan.tgl_akhir', date("d/m/Y"));

    $query = DB::table('v_rep_pengeluaran_bahan as k')
      ->where('k.kode_cabang', $user_cabang);

    // Filter tanggal
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
        $query->whereDate('k.tgl_pengeluaran', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
        $query->whereDate('k.tgl_pengeluaran', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    // $cacheKey = 'lap_pengeluaran_bahan_' . md5($user_cabang . '_' . $tgl_awal . '_' . $tgl_akhir);
    $cacheKey = 'lap_pengeluaran_bahan_v2_' . md5($user_cabang . '_' . $tgl_awal . '_' . $tgl_akhir);

    $datas = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function () use ($query) {
      return $query
        ->select([
          'k.kode_pengeluaran',
          'k.tgl_pengeluaran',
          'k.no_bon',
          'k.kode_spk',
          'k.no_polisi',
          'k.posisi_pekerjaan',
          'k.point_panel',
          'k.kode_barang',
          'k.nama_bahan',
          'k.qty',
          'k.satuan',
          'k.harga_lama',
          'k.harga',
          'k.jumlah'
        ])
        // ->orderBy('k.nama_bahan', 'asc')
        // ->orderBy('k.tgl_pengeluaran', 'asc')
        // ->orderBy('k.kode_pengeluaran', 'asc')
        // ->get();
        ->orderBy('k.tgl_pengeluaran', 'asc')
        ->orderBy('k.kode_pengeluaran', 'asc')
        ->get();
    });

    $totalData = $datas->count();
    $totalFiltered = $totalData;

    $data = [];

    // Hanya proses jika ada data
    if (count($datas) > 0) {
      $no = 0;
      // $currentBahan = null;
      $currentPengeluaran = null;
      $subtotal = [
        'qty' => 0,
        'harga_lama' => 0,
        'harga' => 0,
        'jumlah' => 0,
        'satuan' => ''
      ];

      foreach ($datas as $index => $row) {
        // Jika nama bahan berubah dan bukan data pertama, tambahkan subtotal
        // if ($currentBahan !== null && $currentBahan !== $row->nama_bahan) {
        if ($currentPengeluaran !== null && $currentPengeluaran !== $row->kode_pengeluaran) {
          // Harga Lama berisi penjumlahan harga_lama + harga
          $totalHarga = $subtotal['harga_lama'] + $subtotal['harga'];

          $data[] = [
            'no' => '',
            'kode_pengeluaran' => '',
            'tgl_pengeluaran' => '',
            'no_bon' => '',
            'kode_spk' => '',
            'no_polisi' => '',
            'posisi_pekerjaan' => '',
            'point_panel' => '',
            'kode_barang' => '',
            'nama_bahan' => 'Grand Total', // Nama Bahan berisi "Total"
            'qty' => number_format($subtotal['qty'], 2, '.', '.'),
            'satuan' => $subtotal['satuan'], // Satuan tetap satuan
            'harga_lama' => number_format($totalHarga, 0, '.', '.'), // Penjumlahan harga_lama + harga
            'harga' => '', // Kosong (merged)
            'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
            'is_subtotal' => true
          ];

          // Reset subtotal
          $subtotal = [
            'qty' => 0,
            'harga_lama' => 0,
            'harga' => 0,
            'jumlah' => 0,
            'satuan' => ''
          ];
        }

        // $currentBahan = $row->nama_bahan;
        $currentPengeluaran = $row->kode_pengeluaran;

        // Tambah ke subtotal
        $subtotal['qty'] += $row->qty;
        $subtotal['harga_lama'] += $row->harga_lama;
        $subtotal['harga'] += $row->harga;
        $subtotal['jumlah'] += $row->jumlah;
        $subtotal['satuan'] = $row->satuan; // Simpan satuan

        $data[] = [
          'no' => ++$no,
          'kode_pengeluaran' => $row->kode_pengeluaran,
          'tgl_pengeluaran' => Carbon::parse($row->tgl_pengeluaran)->format('d/m/Y'),
          'no_bon' => $row->no_bon ?? '',
          'kode_spk' => $row->kode_spk ?? '',
          'no_polisi' => $row->no_polisi ?? '',
          'posisi_pekerjaan' => $row->posisi_pekerjaan ?? '',
          'point_panel' => $row->point_panel ?? '',
          'kode_barang' => $row->kode_barang,
          'nama_bahan' => $row->nama_bahan,
          'qty' => number_format($row->qty, 2, '.', '.'),
          'satuan' => $row->satuan ?? '',
          'harga_lama' => number_format($row->harga_lama, 0, '.', '.'),
          'harga' => number_format($row->harga, 0, '.', '.'),
          'jumlah' => number_format($row->jumlah, 0, '.', '.'),
          'is_subtotal' => false
        ];
      }

      // Tambahkan subtotal terakhir (hanya jika ada data)
      $totalHarga = $subtotal['harga_lama'] + $subtotal['harga'];

      $data[] = [
        'no' => '',
        'kode_pengeluaran' => '',
        'tgl_pengeluaran' => '',
        'no_bon' => '',
        'kode_spk' => '',
        'no_polisi' => '',
        'posisi_pekerjaan' => '',
        'point_panel' => '',
        'kode_barang' => '',
        'nama_bahan' => 'Total', // Nama Bahan berisi "Total"
        'qty' => number_format($subtotal['qty'], 2, '.', '.'),
        'satuan' => $subtotal['satuan'], // Satuan tetap satuan
        'harga_lama' => number_format($totalHarga, 0, '.', '.'), // Penjumlahan harga_lama + harga
        'harga' => '', // Kosong (merged)
        'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
        'is_subtotal' => true
      ];
    }

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

    return redirect('gudang/laporan-pengeluaran-bahan')->with('datafilter_pengeluaran_bahan', $dataArray);
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

    // Format Periode
    $tglAwal = $request->input('tgl_awal', date('d/m/Y'));
    $tglAkhir = $request->input('tgl_akhir', date('d/m/Y'));
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    $fileName = 'Laporan_Pengeluaran_Bahan_' . date('Ymd_His') . '.xlsx';

    return Excel::download(
      new LaporanPengeluaranBahanExport($filters, $cabangData, $periodeStr),
      $fileName
    );
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    $user_cabang = session('kd_cabang');
    $namaCabang = session('nm_cabang');

    $title = 'Laporan Pengeluaran Bahan';
    $filters = $request->all();

    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rep_pengeluaran_bahan as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.kode_pengeluaran',
        'k.tgl_pengeluaran',
        'k.no_bon',
        'k.kode_spk',
        'k.no_polisi',
        'k.posisi_pekerjaan',
        'k.point_panel',
        'k.kode_barang',
        'k.nama_bahan',
        'k.qty',
        'k.satuan',
        'k.harga_lama',
        'k.harga',
        'k.jumlah'
      ]);

    // Filter tanggal
    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('k.tgl_pengeluaran', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('k.tgl_pengeluaran', '<=', $endDate);
    }

    // $datas = $query->orderBy('k.nama_bahan', 'asc')->orderBy('k.tgl_pengeluaran', 'asc')->orderBy('k.kode_pengeluaran', 'asc')->get();
    $datas = $query->orderBy('k.tgl_pengeluaran', 'asc')
      ->orderBy('k.kode_pengeluaran', 'asc')
      ->get();

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.laporan.laporan-pengeluaran-bahan-print', [
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
