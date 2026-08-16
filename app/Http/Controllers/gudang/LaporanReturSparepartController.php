<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanReturSparepartExport;

class LaporanReturSparepartController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanReturSparepart(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Laporan Retur Sparepart';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_retur_sparepart');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.gudang.laporan.laporan-retur-sparepart', [
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
  //   // Yang membuat tidak error meskipun view belum ada: try-catch di method index()
  //   // Jika view 'v_rep_retur_sparepart' belum ada di database, akan masuk ke catch dan return data kosong
  //   try {
  //     $user_cabang = session('kd_cabang');

  //     $tgl_awal = $request->filled('tgl_awal')
  //       ? $request->tgl_awal
  //       : session('datafilter_retur_sparepart.tgl_awal', date("d/m/Y"));

  //     $tgl_akhir = $request->filled('tgl_akhir')
  //       ? $request->tgl_akhir
  //       : session('datafilter_retur_sparepart.tgl_akhir', date("d/m/Y"));

  //     $query = DB::table('v_rep_retur_sparepart as k')
  //       ->where('k.kode_cabang', $user_cabang);

  //     // Filter tanggal
  //     if (!empty($tgl_awal)) {
  //       try {
  //         $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
  //         $query->whereDate('k.tgl_retur', '>=', $startDate);
  //       } catch (\Exception $e) {
  //         // Handle error
  //       }
  //     }

  //     if (!empty($tgl_akhir)) {
  //       try {
  //         $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
  //         $query->whereDate('k.tgl_retur', '<=', $endDate);
  //       } catch (\Exception $e) {
  //         // Handle error
  //       }
  //     }

  //     $totalData = (clone $query)->count();
  //     $totalFiltered = (clone $query)->count();

  //     $datas = $query
  //       ->select([
  //         'k.tgl_retur',
  //         'k.no_retur',
  //         'k.tgl_input_gudang',
  //         'k.no_input_gudang',
  //         'k.nama_supplier',
  //         'k.nama_sparepart',
  //         'k.no_spk',
  //         'k.no_polisi',
  //         'k.jumlah',
  //         'k.harga',
  //         'k.total'
  //       ])
  //       ->orderBy('k.nama_sparepart', 'asc')
  //       ->orderBy('k.tgl_retur', 'asc')
  //       ->orderBy('k.no_retur', 'asc')
  //       ->get();

  //     $data = [];

  //     // Hanya proses jika ada data
  //     if (count($datas) > 0) {
  //       $no = 0;
  //       $currentSparepart = null;
  //       $subtotal = [
  //         'jumlah' => 0,
  //         'harga' => 0,
  //         'total' => 0
  //       ];

  //       foreach ($datas as $index => $row) {
  //         // Jika nama sparepart berubah dan bukan data pertama, tambahkan subtotal
  //         if ($currentSparepart !== null && $currentSparepart !== $row->nama_sparepart) {
  //           $data[] = [
  //             'no' => '',
  //             'tgl_retur' => '',
  //             'no_retur' => '',
  //             'tgl_input_gudang' => '',
  //             'no_input_gudang' => '',
  //             'nama_supplier' => '',
  //             'nama_sparepart' => 'Total',
  //             'no_spk' => '',
  //             'no_polisi' => '',
  //             'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
  //             'harga' => number_format($subtotal['harga'], 0, '.', '.'),
  //             'total' => number_format($subtotal['total'], 0, '.', '.'),
  //             'is_subtotal' => true
  //           ];

  //           // Reset subtotal
  //           $subtotal = [
  //             'jumlah' => 0,
  //             'harga' => 0,
  //             'total' => 0
  //           ];
  //         }

  //         $currentSparepart = $row->nama_sparepart;

  //         // Tambah ke subtotal
  //         $subtotal['jumlah'] += $row->jumlah;
  //         $subtotal['harga'] += $row->harga;
  //         $subtotal['total'] += $row->total;

  //         $data[] = [
  //           'no' => ++$no,
  //           'tgl_retur' => Carbon::parse($row->tgl_retur)->format('d/m/Y'),
  //           'no_retur' => $row->no_retur,
  //           'tgl_input_gudang' => $row->tgl_input_gudang ? Carbon::parse($row->tgl_input_gudang)->format('d/m/Y') : '',
  //           'no_input_gudang' => $row->no_input_gudang ?? '',
  //           'nama_supplier' => $row->nama_supplier ?? '',
  //           'nama_sparepart' => $row->nama_sparepart,
  //           'no_spk' => $row->no_spk ?? '',
  //           'no_polisi' => $row->no_polisi ?? '',
  //           'jumlah' => number_format($row->jumlah, 0, '.', '.'),
  //           'harga' => number_format($row->harga, 0, '.', '.'),
  //           'total' => number_format($row->total, 0, '.', '.'),
  //           'is_subtotal' => false
  //         ];
  //       }

  //       // Tambahkan subtotal terakhir
  //       $data[] = [
  //         'no' => '',
  //         'tgl_retur' => '',
  //         'no_retur' => '',
  //         'tgl_input_gudang' => '',
  //         'no_input_gudang' => '',
  //         'nama_supplier' => '',
  //         'nama_sparepart' => 'Total',
  //         'no_spk' => '',
  //         'no_polisi' => '',
  //         'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
  //         'harga' => number_format($subtotal['harga'], 0, '.', '.'),
  //         'total' => number_format($subtotal['total'], 0, '.', '.'),
  //         'is_subtotal' => true
  //       ];
  //     }

  //     return response()->json([
  //       'draw' => intval($request->input('draw')),
  //       'recordsTotal' => intval($totalData),
  //       'recordsFiltered' => intval($totalFiltered),
  //       'data' => $data,
  //     ]);

  //   } catch (\Exception $e) {
  //     // Jika ada error (view belum ada), return data kosong. Tabel akan tampil "No data available"
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

      $tgl_awal = $request->filled('tgl_awal')
        ? $request->tgl_awal
        : session('datafilter_retur_sparepart.tgl_awal', date("d/m/Y"));

      $tgl_akhir = $request->filled('tgl_akhir')
        ? $request->tgl_akhir
        : session('datafilter_retur_sparepart.tgl_akhir', date("d/m/Y"));

      $query = DB::table('v_rep_retur_sparepart as k')
        ->where('k.kode_cabang', $user_cabang);

      if (!empty($tgl_awal)) {
        try {
          $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
          $query->whereDate('k.tgl_retur', '>=', $startDate);
        } catch (\Exception $e) {
        }
      }

      if (!empty($tgl_akhir)) {
        try {
          $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
          $query->whereDate('k.tgl_retur', '<=', $endDate);
        } catch (\Exception $e) {
        }
      }

      $totalData = (clone $query)->count();
      $totalFiltered = (clone $query)->count();

      $datas = $query
        ->select([
          'k.tgl_retur',
          'k.no_retur',
          'k.tgl_input_gudang',
          'k.no_input_gudang',
          'k.nama_supplier',
          'k.nama_sparepart',
          'k.no_spk',
          'k.no_polisi',
          'k.jumlah',
          'k.harga',
          'k.total'
        ])
        // Sorting berdasarkan tanggal & nomor retur (bukan nama sparepart)
        ->orderBy('k.tgl_retur', 'asc')
        ->orderBy('k.no_retur', 'asc')
        ->get();

      $data = [];

      if (count($datas) > 0) {
        $no = 0;
        $currentNoRetur = null; // grouping berdasarkan no_retur
        $subtotal = [
          'jumlah' => 0,
          'harga' => 0,
          'total' => 0
        ];

        foreach ($datas as $index => $row) {
          // Jika no_retur berubah dan bukan data pertama, tambahkan subtotal
          if ($currentNoRetur !== null && $currentNoRetur !== $row->no_retur) {
            $data[] = [
              'no' => '',
              'tgl_retur' => '',
              'no_retur' => '',
              'tgl_input_gudang' => '',
              'no_input_gudang' => '',
              'nama_supplier' => '',
              'nama_sparepart' => 'Total',
              'no_spk' => '',
              'no_polisi' => '',
              'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
              'harga' => number_format($subtotal['harga'], 0, '.', '.'),
              'total' => number_format($subtotal['total'], 0, '.', '.'),
              'is_subtotal' => true
            ];

            $subtotal = ['jumlah' => 0, 'harga' => 0, 'total' => 0];
          }

          $currentNoRetur = $row->no_retur;

          $subtotal['jumlah'] += $row->jumlah;
          $subtotal['harga'] += $row->harga;
          $subtotal['total'] += $row->total;

          $data[] = [
            'no' => ++$no,
            'tgl_retur' => Carbon::parse($row->tgl_retur)->format('d/m/Y'),
            'no_retur' => $row->no_retur,
            'tgl_input_gudang' => $row->tgl_input_gudang ? Carbon::parse($row->tgl_input_gudang)->format('d/m/Y') : '',
            'no_input_gudang' => $row->no_input_gudang ?? '',
            'nama_supplier' => $row->nama_supplier ?? '',
            'nama_sparepart' => $row->nama_sparepart,
            'no_spk' => $row->no_spk ?? '',
            'no_polisi' => $row->no_polisi ?? '',
            'jumlah' => number_format($row->jumlah, 0, '.', '.'),
            'harga' => number_format($row->harga, 0, '.', '.'),
            'total' => number_format($row->total, 0, '.', '.'),
            'is_subtotal' => false
          ];
        }

        $data[] = [
          'no' => '',
          'tgl_retur' => '',
          'no_retur' => '',
          'tgl_input_gudang' => '',
          'no_input_gudang' => '',
          'nama_supplier' => '',
          'nama_sparepart' => 'Grand Total',
          'no_spk' => '',
          'no_polisi' => '',
          'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
          'harga' => number_format($subtotal['harga'], 0, '.', '.'),
          'total' => number_format($subtotal['total'], 0, '.', '.'),
          'is_subtotal' => true
        ];
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $data,
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
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

    return redirect('gudang/laporan-retur-sparepart')->with('datafilter_retur_sparepart', $dataArray);
  }

  /**
   * Export data to Excel.
   */
  public function exportExcel(Request $request)
  {
    // Yang membuat tidak error meskipun view belum ada: try-catch di method exportExcel()
    try {
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

      $fileName = 'Laporan_Retur_Sparepart_' . date('Ymd_His') . '.xlsx';

      return Excel::download(
        new LaporanReturSparepartExport($filters, $cabangData, $periodeStr),
        $fileName
      );
    } catch (\Exception $e) {
      // Jika error (view belum ada), redirect dengan pesan error
      return redirect()->back()->with('error', 'View database belum tersedia. Silakan hubungi administrator.');
    }
  }

  /**
   * Print data.
   */
  public function printData(Request $request)
  {
    // Yang membuat tidak error meskipun view belum ada: try-catch di method printData()
    try {
      $user_cabang = session('kd_cabang');
      $namaCabang = session('nm_cabang');

      $title = 'Laporan Retur Sparepart';
      $filters = $request->all();

      $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
      $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
      $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

      $query = DB::table('v_rep_retur_sparepart as k')
        ->where('k.kode_cabang', $user_cabang)
        ->select([
          'k.tgl_retur',
          'k.no_retur',
          'k.tgl_input_gudang',
          'k.no_input_gudang',
          'k.nama_supplier',
          'k.nama_sparepart',
          'k.no_spk',
          'k.no_polisi',
          'k.jumlah',
          'k.harga',
          'k.total'
        ]);

      if (!empty($filters['tgl_awal'])) {
        $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
        $query->whereDate('k.tgl_retur', '>=', $startDate);
      }

      if (!empty($filters['tgl_akhir'])) {
        $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
        $query->whereDate('k.tgl_retur', '<=', $endDate);
      }

      // $datas = $query
      //   ->orderBy('k.nama_sparepart', 'asc')
      //   ->orderBy('k.tgl_retur', 'asc')
      //   ->orderBy('k.no_retur', 'asc')
      //   ->get();
      $datas = $query
        ->orderBy('k.tgl_retur', 'asc')
        ->orderBy('k.no_retur', 'asc')
        ->get();

      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.gudang.laporan.laporan-retur-sparepart-print', [
        'title' => $title,
        'namaCabang' => $namaCabang,
        'periodeStr' => $periodeStr,
        'no' => 1,
        'datas' => $datas,
        'datafilter' => $filters,
        'pageConfigs' => $pageConfigs,
      ]);
    } catch (\Exception $e) {
      // Jika error (view belum ada), redirect dengan pesan error
      return redirect()->back()->with('error', 'View database belum tersedia. Silakan hubungi administrator.');
    }
  }
}
