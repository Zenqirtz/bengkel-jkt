<?php

namespace App\Http\Controllers\gudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanPembelianCatExport;

use App\Helpers\Helpers as Helper;

class LaporanPembelianCatController extends Controller
{
  /**
   * Redirect to view.
   */
  public function LaporanPembelianCat(): View
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
    $title = Helper::getTitleMenu($path) ?? 'Laporan Pembelian Cat';
    $user_cabang = session('kd_cabang');

    $datafilter = session('datafilter_cat');
    if (empty($datafilter)) {
      $datafilter['supplier'] = '';
      $datafilter['tgl_awal'] = date("d/m/Y");
      $datafilter['tgl_akhir'] = date("d/m/Y");
    }

    // Get data pemasok untuk dropdown
    $pemasok = DB::table('m_pemasok')
      ->where('kode_cabang', $user_cabang)
      ->select('kode_pemasok', 'nama_pemasok')
      ->orderBy('nama_pemasok', 'asc')
      ->get();

    return view('content.gudang.laporan.laporan-pembelian-cat', [
      'title' => $title,
      'isList' => $isList,
      'isAdd' => $isAdd,
      'isEdit' => $isEdit,
      'isDel' => $isDel,
      'user_cabang' => $user_cabang,
      'datafilter' => $datafilter,
      'pemasok' => $pemasok,
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

    $supplier = $request->filled('supplier')
      ? $request->supplier
      : session('datafilter_cat.supplier', '');

    $tgl_awal = $request->filled('tgl_awal')
      ? $request->tgl_awal
      : session('datafilter_cat.tgl_awal', date("d/m/Y"));

    $tgl_akhir = $request->filled('tgl_akhir')
      ? $request->tgl_akhir
      : session('datafilter_cat.tgl_akhir', date("d/m/Y"));

    $query = DB::table('v_rep_pembelian_cat as k')
      ->where('k.kode_cabang', $user_cabang);

    // Filter supplier
    if (!empty($supplier)) {
      $query->where('k.kode_pemasok', $supplier);
    }

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

    $totalData = (clone $query)->count();
    $totalFiltered = (clone $query)->count();

    $datas = $query
      ->select([
        'k.tanggal',
        'k.kode_input',
        'k.no_po',
        'k.nama_pemasok',
        'k.kode_bahan',
        'k.nama_bahan',
        'k.qty',
        'k.kode_satuan',
        'k.harga',
        'k.jumlah_sebelum',
        'k.ppn',
        'k.jumlah'
      ])
      ->orderBy('k.tanggal', 'asc')
      ->orderBy('k.kode_input', 'asc')
      ->get();

    // $data = [];
    // $no = 0;

    // foreach ($datas as $row) {
    //   $data[] = [
    //     'no' => ++$no,
    //     'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
    //     'kode_input' => $row->kode_input,
    //     'no_po' => $row->no_po ?? '',
    //     'nama_pemasok' => $row->nama_pemasok,
    //     'kode_bahan' => $row->kode_bahan,
    //     'nama_bahan' => $row->nama_bahan,
    //     'unit' => number_format($row->qty, 2, '.', '.'),
    //     'kode_satuan' => $row->kode_satuan,
    //     'harga' => number_format($row->harga, 0, '.', '.'),
    //     'jumlah' => number_format($row->jumlah_sebelum, 0, '.', '.'),
    //     'ppn' => number_format($row->ppn, 0, '.', '.'),
    //     'total' => number_format($row->jumlah, 0, '.', '.'),
    //   ];
    // }
    $data = [];
    $no = 0;
    $grandTotal = ['unit' => 0, 'jumlah' => 0, 'ppn' => 0, 'total' => 0];

    foreach ($datas as $row) {
      $grandTotal['unit'] += $row->qty;
      $grandTotal['jumlah'] += $row->jumlah_sebelum;
      $grandTotal['ppn'] += $row->ppn;
      $grandTotal['total'] += $row->jumlah;

      $data[] = [
        'no' => ++$no,
        'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
        'kode_input' => $row->kode_input,
        'no_po' => $row->no_po ?? '',
        'nama_pemasok' => $row->nama_pemasok,
        'kode_bahan' => $row->kode_bahan,
        'nama_bahan' => $row->nama_bahan,
        'unit' => number_format($row->qty, 2, '.', '.'),
        'kode_satuan' => $row->kode_satuan,
        'harga' => number_format($row->harga, 0, '.', '.'),
        'jumlah' => number_format($row->jumlah_sebelum, 0, '.', '.'),
        'ppn' => number_format($row->ppn, 0, '.', '.'),
        'total' => number_format($row->jumlah, 0, '.', '.'),
      ];
    }

    if (count($datas) > 0) {
      $data[] = [
        'no' => '',
        'tanggal' => '',
        'kode_input' => 'Grand Total',
        'no_po' => '',
        'nama_pemasok' => '',
        'kode_bahan' => '',
        'nama_bahan' => '',
        'unit' => number_format($grandTotal['unit'], 2, '.', '.'),
        'kode_satuan' => '',
        'harga' => '',
        'jumlah' => number_format($grandTotal['jumlah'], 0, '.', '.'),
        'ppn' => number_format($grandTotal['ppn'], 0, '.', '.'),
        'total' => number_format($grandTotal['total'], 0, '.', '.'),
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

    $dataArray['supplier'] = $request->supplier ?? '';
    $dataArray['tgl_awal'] = $request->tgl_awal;
    $dataArray['tgl_akhir'] = $request->tgl_akhir;

    return redirect('gudang/laporan-pembelian-cat')->with('datafilter_cat', $dataArray);
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

    $fileName = 'Laporan_Pembelian_Cat_' . date('Ymd_His') . '.xlsx';

    return Excel::download(
      new LaporanPembelianCatExport($filters, $cabangData, $periodeStr),
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

    $title = 'Laporan Pembelian Cat';
    $filters = $request->all();

    $supplier = $filters['supplier'] ?? '';
    $tglAwal = $filters['tgl_awal'] ?? date('d/m/Y');
    $tglAkhir = $filters['tgl_akhir'] ?? date('d/m/Y');
    $periodeStr = $tglAwal . ' s/d ' . $tglAkhir;

    // Query data
    $query = DB::table('v_rep_pembelian_cat as k')
      ->where('k.kode_cabang', $user_cabang)
      ->select([
        'k.tanggal',
        'k.kode_input',
        'k.no_po',
        'k.nama_pemasok',
        'k.kode_bahan',
        'k.nama_bahan',
        'k.qty',
        'k.kode_satuan',
        'k.harga',
        'k.jumlah_sebelum',
        'k.ppn',
        'k.jumlah'
      ]);

    // Filter supplier
    if (!empty($supplier)) {
      $query->where('k.kode_pemasok', $supplier);
    }

    // Filter tanggal
    if (!empty($filters['tgl_awal'])) {
      $startDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_awal'])->format('Y-m-d');
      $query->whereDate('k.tanggal', '>=', $startDate);
    }

    if (!empty($filters['tgl_akhir'])) {
      $endDate = Carbon::createFromFormat('d/m/Y', $filters['tgl_akhir'])->format('Y-m-d');
      $query->whereDate('k.tanggal', '<=', $endDate);
    }

    $datas = $query->orderBy('k.tanggal', 'asc')->orderBy('k.kode_input', 'asc')->get();

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.gudang.laporan.laporan-pembelian-cat-print', [
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
