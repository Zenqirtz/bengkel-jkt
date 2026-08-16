<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanGudangSparepartExport;

class LaporanGudangSparepartController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanGudangSparepart(): View
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
    $title = \Helper::getTitleMenu($path) ?? 'Laporan Gudang Sparepart';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_gudang_sparepart');
    if (empty($datafilter)) {
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    return view('content.gudang.laporan.laporan-gudang-sparepart', [
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

    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter_gudang_sparepart.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter_gudang_sparepart.tgl_akhir', date("d/m/Y"));

    $query = DB::table('v_rpt_gudang_sparepart as k')
      ->where('k.kode_cabang', $user_cabang);

    // Filter tanggal
    if (!empty($tgl_awal)) {
      try {
        $startDate = Carbon::createFromFormat('d/m/Y', $tgl_awal)->format('Y-m-d');
        $query->whereDate('k.tanggal', '>=', $startDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    if (!empty($tgl_akhir)) {
      try {
        $endDate = Carbon::createFromFormat('d/m/Y', $tgl_akhir)->format('Y-m-d');
        $query->whereDate('k.tanggal', '<=', $endDate);
      } catch (\Exception $e) {
        // Handle error
      }
    }

    // $totalData = (clone $query)->count();
    // $totalFiltered = (clone $query)->count();

    // $datas = $query
    //   ->select([
    //     'k.tanggal',
    //     'k.kode_input',
    //     'k.nama_pemasok',
    //     'k.nama_sparepart',
    //     'k.no_po',
    //     'k.qty',
    //     'k.harga',
    //     'k.jumlah_sebelum',
    //     'k.ppn',
    //     'k.jumlah',
    //     'k.cash',
    //     'k.credit',
    //     'k.merek_tipe',
    //     'k.kode_spk',
    //     'k.no_polisi'
    //   ])
    //   ->orderBy('k.tanggal', 'asc')
    //   ->orderBy('k.kode_input', 'asc')
    //   ->get();

    // $data = [];

    // // Hanya proses jika ada data
    // if (count($datas) > 0) {
    //   $no = 0;
    //   // $currentSupplier = null;
    //   $currentKodeInput = null;
    //   $subtotal = [
    //     'harga' => 0,
    //     'jumlah_sebelum' => 0,
    //     'ppn' => 0,
    //     'jumlah' => 0,
    //     'cash' => 0,
    //     'credit' => 0
    //   ];

    //   $grandTotal = [
    //     'jumlah_sebelum' => 0,
    //     'ppn' => 0,
    //     'jumlah' => 0,
    //     'cash' => 0,
    //     'credit' => 0
    //   ];

    //   foreach ($datas as $index => $row) {
    //     // Jika supplier berubah dan bukan data pertama, tambahkan subtotal
    //     // if ($currentSupplier !== null && $currentSupplier !== $row->nama_pemasok) {
    //     if ($currentKodeInput !== null && $currentKodeInput !== $row->kode_input) {
    //       $data[] = [
    //         'no' => '',
    //         'tanggal' => '',
    //         'kode_input' => '',
    //         'nama_pemasok' => '',
    //         'nama_sparepart' => '',
    //         'no_po' => '',
    //         'qty' => '',
    //         'harga' => 'Total',
    //         'jumlah_sebelum' => number_format($subtotal['jumlah_sebelum'], 0, '.', '.'),
    //         'ppn' => number_format($subtotal['ppn'], 0, '.', '.'),
    //         'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
    //         'cash' => number_format($subtotal['cash'], 0, '.', '.'),
    //         'credit' => number_format($subtotal['credit'], 0, '.', '.'),
    //         'merek_tipe' => '',
    //         'kode_spk' => '',
    //         'no_polisi' => '',
    //         'is_subtotal' => true
    //       ];

    //       $grandTotal['jumlah_sebelum'] += $subtotal['jumlah_sebelum'];
    //       $grandTotal['ppn'] += $subtotal['ppn'];
    //       $grandTotal['jumlah'] += $subtotal['jumlah'];
    //       $grandTotal['cash'] += $subtotal['cash'];
    //       $grandTotal['credit'] += $subtotal['credit'];

    //       // Reset subtotal
    //       $subtotal = [
    //         'harga' => 0,
    //         'jumlah_sebelum' => 0,
    //         'ppn' => 0,
    //         'jumlah' => 0,
    //         'cash' => 0,
    //         'credit' => 0
    //       ];
    //     }

    //     // $currentSupplier = $row->nama_pemasok;
    //     $currentKodeInput = $row->kode_input;

    //     // Tambah ke subtotal
    //     $subtotal['harga'] += $row->harga;
    //     $subtotal['jumlah_sebelum'] += $row->jumlah_sebelum;
    //     $subtotal['ppn'] += $row->ppn;
    //     $subtotal['jumlah'] += $row->jumlah;
    //     $subtotal['cash'] += $row->cash;
    //     $subtotal['credit'] += $row->credit;

    //     $data[] = [
    //       'no' => ++$no,
    //       'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
    //       'kode_input' => $row->kode_input,
    //       'nama_pemasok' => $row->nama_pemasok,
    //       'nama_sparepart' => $row->nama_sparepart,
    //       'no_po' => $row->no_po ?? '',
    //       'qty' => number_format($row->qty, 2, '.', '.'),
    //       'harga' => number_format($row->harga, 0, '.', '.'),
    //       'jumlah_sebelum' => number_format($row->jumlah_sebelum, 0, '.', '.'),
    //       'ppn' => number_format($row->ppn, 0, '.', '.'),
    //       'jumlah' => number_format($row->jumlah, 0, '.', '.'),
    //       'cash' => number_format($row->cash, 0, '.', '.'),
    //       'credit' => number_format($row->credit, 0, '.', '.'),
    //       'merek_tipe' => $row->merek_tipe ?? '',
    //       'kode_spk' => $row->kode_spk ?? '',
    //       'no_polisi' => $row->no_polisi ?? '',
    //       'is_subtotal' => false
    //     ];
    //   }

    //   // Tambahkan subtotal terakhir (hanya jika ada data)
    //   $data[] = [
    //     'no' => '',
    //     'tanggal' => '',
    //     'kode_input' => '',
    //     'nama_pemasok' => '',
    //     'nama_sparepart' => '',
    //     'no_po' => '',
    //     'qty' => '',
    //     'harga' => 'Total',
    //     'jumlah_sebelum' => number_format($subtotal['jumlah_sebelum'], 0, '.', '.'),
    //     'ppn' => number_format($subtotal['ppn'], 0, '.', '.'),
    //     'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
    //     'cash' => number_format($subtotal['cash'], 0, '.', '.'),
    //     'credit' => number_format($subtotal['credit'], 0, '.', '.'),
    //     'merek_tipe' => '',
    //     'kode_spk' => '',
    //     'no_polisi' => '',
    //     'is_subtotal' => true
    //   ];

    //   $grandTotal['jumlah_sebelum'] += $subtotal['jumlah_sebelum'];
    //   $grandTotal['ppn'] += $subtotal['ppn'];
    //   $grandTotal['jumlah'] += $subtotal['jumlah'];
    //   $grandTotal['cash'] += $subtotal['cash'];
    //   $grandTotal['credit'] += $subtotal['credit'];

    //   // Tambahkan Grand Total
    //   $data[] = [
    //     'no' => '',
    //     'tanggal' => '',
    //     'kode_input' => '',
    //     'nama_pemasok' => '',
    //     'nama_sparepart' => '',
    //     'no_po' => '',
    //     'qty' => '',
    //     'harga' => 'Grand Total',
    //     'jumlah_sebelum' => number_format($grandTotal['jumlah_sebelum'], 0, '.', '.'),
    //     'ppn' => number_format($grandTotal['ppn'], 0, '.', '.'),
    //     'jumlah' => number_format($grandTotal['jumlah'], 0, '.', '.'),
    //     'cash' => number_format($grandTotal['cash'], 0, '.', '.'),
    //     'credit' => number_format($grandTotal['credit'], 0, '.', '.'),
    //     'merek_tipe' => '',
    //     'kode_spk' => '',
    //     'no_polisi' => '',
    //     'is_subtotal' => true,
    //     'is_grandtotal' => true
    //   ];
    // }

    // return response()->json([
    //   'draw' => intval($request->input('draw')),
    //   'recordsTotal' => intval($totalData),
    //   'recordsFiltered' => intval($totalFiltered),
    //   'data' => $data,
    // ]);
    $totalData = (clone $query)->count();

    $datas = $query
      ->select([
        'k.tanggal',
        'k.kode_input',
        'k.nama_pemasok',
        'k.nama_sparepart',
        'k.no_po',
        'k.qty',
        'k.harga',
        'k.jumlah_sebelum',
        'k.ppn',
        'k.jumlah',
        'k.cash',
        'k.credit',
        'k.merek_tipe',
        'k.kode_spk',
        'k.no_polisi'
      ])
      ->orderBy('k.tanggal', 'asc')
      ->orderBy('k.kode_input', 'asc')
      ->get();

    // TAMBAHAN: kelompokkan per kode_input dulu
    $groups = $datas->groupBy('kode_input');
    $totalFiltered = $groups->count(); // total GROUP, bukan total baris

    // TAMBAHAN: pagination diterapkan ke GROUP, bukan ke baris
    $start = (int) $request->input('start', 0);
    $limit = (int) $request->input('length', 10);

    $pagedGroups = $limit == -1
      ? $groups
      : $groups->slice($start, $limit);

    $data = [];
    $no = $start; // supaya nomor urut lanjut sesuai halaman (opsional, bisa juga direset per halaman)

    $grandTotal = ['jumlah_sebelum' => 0, 'ppn' => 0, 'jumlah' => 0, 'cash' => 0, 'credit' => 0];

    // Hitung grand total dari SELURUH data (semua group), bukan hanya yang tampil
    foreach ($groups as $groupRows) {
      foreach ($groupRows as $row) {
        $grandTotal['jumlah_sebelum'] += $row->jumlah_sebelum;
        $grandTotal['ppn'] += $row->ppn;
        $grandTotal['jumlah'] += $row->jumlah;
        $grandTotal['cash'] += $row->cash;
        $grandTotal['credit'] += $row->credit;
      }
    }

    // Bangun $data HANYA dari group yang tampil di halaman ini
    foreach ($pagedGroups as $kodeInput => $groupRows) {
      $subtotal = ['jumlah_sebelum' => 0, 'ppn' => 0, 'jumlah' => 0, 'cash' => 0, 'credit' => 0];

      foreach ($groupRows as $row) {
        $no++;
        $subtotal['jumlah_sebelum'] += $row->jumlah_sebelum;
        $subtotal['ppn'] += $row->ppn;
        $subtotal['jumlah'] += $row->jumlah;
        $subtotal['cash'] += $row->cash;
        $subtotal['credit'] += $row->credit;

        $data[] = [
          'no' => $no,
          'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
          'kode_input' => $row->kode_input,
          'nama_pemasok' => $row->nama_pemasok,
          'nama_sparepart' => $row->nama_sparepart,
          'no_po' => $row->no_po ?? '',
          'qty' => number_format($row->qty, 2, '.', '.'),
          'harga' => number_format($row->harga, 0, '.', '.'),
          'jumlah_sebelum' => number_format($row->jumlah_sebelum, 0, '.', '.'),
          'ppn' => number_format($row->ppn, 0, '.', '.'),
          'jumlah' => number_format($row->jumlah, 0, '.', '.'),
          'cash' => number_format($row->cash, 0, '.', '.'),
          'credit' => number_format($row->credit, 0, '.', '.'),
          'merek_tipe' => $row->merek_tipe ?? '',
          'kode_spk' => $row->kode_spk ?? '',
          'no_polisi' => $row->no_polisi ?? '',
          'is_subtotal' => false
        ];
      }

      // Baris subtotal per group
      $data[] = [
        'no' => '',
        'tanggal' => '',
        'kode_input' => '',
        'nama_pemasok' => '',
        'nama_sparepart' => '',
        'no_po' => '',
        'qty' => '',
        'harga' => 'Total',
        'jumlah_sebelum' => number_format($subtotal['jumlah_sebelum'], 0, '.', '.'),
        'ppn' => number_format($subtotal['ppn'], 0, '.', '.'),
        'jumlah' => number_format($subtotal['jumlah'], 0, '.', '.'),
        'cash' => number_format($subtotal['cash'], 0, '.', '.'),
        'credit' => number_format($subtotal['credit'], 0, '.', '.'),
        'merek_tipe' => '',
        'kode_spk' => '',
        'no_polisi' => '',
        'is_subtotal' => true
      ];
    }

   // Grand Total hanya ditambahkan di halaman terakhir DAN jika ada data
    $isLastPage = $limit == -1 || ($start + $limit) >= $totalFiltered;

    if ($isLastPage && $totalFiltered > 0) {   // TAMBAHAN: cek $totalFiltered > 0
      $data[] = [
        'no' => '',
        'tanggal' => '',
        'kode_input' => '',
        'nama_pemasok' => '',
        'nama_sparepart' => '',
        'no_po' => '',
        'qty' => '',
        'harga' => 'Grand Total',
        'jumlah_sebelum' => number_format($grandTotal['jumlah_sebelum'], 0, '.', '.'),
        'ppn' => number_format($grandTotal['ppn'], 0, '.', '.'),
        'jumlah' => number_format($grandTotal['jumlah'], 0, '.', '.'),
        'cash' => number_format($grandTotal['cash'], 0, '.', '.'),
        'credit' => number_format($grandTotal['credit'], 0, '.', '.'),
        'merek_tipe' => '',
        'kode_spk' => '',
        'no_polisi' => '',
        'is_subtotal' => true,
        'is_grandtotal' => true
      ];
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered), // sekarang = jumlah GROUP, cocok dgn "of X entries"
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

    return redirect('gudang/laporan-gudang-sparepart')->with('datafilter_gudang_sparepart', $dataArray);
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

    $fileName = 'Laporan_Gudang_Sparepart_' . date('Ymd_His') . '.xlsx';

    return Excel::download(
      new LaporanGudangSparepartExport($filters, $cabangData, $periodeStr),
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

    $title = 'Laporan Gudang Sparepart';
    $filters = $request->all();

    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rpt_gudang_sparepart as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.tanggal',
        'k.kode_input',
        'k.nama_pemasok',
        'k.nama_sparepart',
        'k.no_po',
        'k.qty',
        'k.harga',
        'k.jumlah_sebelum',
        'k.ppn',
        'k.jumlah',
        'k.cash',
        'k.credit',
        'k.merek_tipe',
        'k.kode_spk',
        'k.no_polisi'
      ]);

    // Filter tanggal
    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('k.tanggal', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('k.tanggal', '<=', $endDate);
    }

    // $datas = $query->orderBy('k.nama_pemasok', 'asc')->orderBy('k.tanggal', 'asc')->orderBy('k.kode_input', 'asc')->get();
    $datas = $query->orderBy('k.tanggal', 'asc')->orderBy('k.kode_input', 'asc')->get();

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.laporan.laporan-gudang-sparepart-print', [
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
